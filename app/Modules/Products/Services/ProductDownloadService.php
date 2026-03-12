<?php

namespace App\Modules\Products\Services;

use App\Models\User;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductVerificationSource;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductDownload;
use App\Modules\Products\Models\ProductDownloadAccess;
use App\Modules\Products\Models\ProductUserVerification;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductDownloadService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function authorize(User $user, Product $product, ProductDownload $download): bool
    {
        if (! $user->isActiveForAuthentication() || ! $user->hasPermission('downloads.protected.access')) {
            return false;
        }

        if ($download->product_id !== $product->getKey()) {
            return false;
        }

        $version = $product->currentPublishedVersion;

        if (! $version || $download->product_version_id !== $version->getKey()) {
            return false;
        }

        if ($product->visibility === ProductVisibility::Private && ! $user->isInternalStaff() && ! $this->hasVerification($user, $product)) {
            return false;
        }

        return match ($download->visibility) {
            ProductDownloadVisibility::Authenticated => true,
            ProductDownloadVisibility::Verified => $user->hasVerifiedEmail(),
            ProductDownloadVisibility::Internal => $user->isInternalStaff(),
            ProductDownloadVisibility::Entitled => $this->hasVerification($user, $product),
        };
    }

    /**
     * @return RedirectResponse|Response|StreamedResponse
     */
    public function handle(User $user, Product $product, ProductDownload $download)
    {
        $access = $this->recordAttempt($user, $product, $download);

        $deniedReason = $this->deniedReason($user, $product, $download);

        if ($deniedReason !== null) {
            $access->forceFill([
                'access_granted' => false,
                'denied_reason' => $deniedReason,
            ])->save();

            $this->auditLogService->record(
                eventType: 'products.download.denied',
                action: 'deny_product_download',
                actor: $user,
                auditable: $download,
                metadata: [
                    'product_id' => $product->getKey(),
                    'download_access_id' => $access->getKey(),
                    'denied_reason' => $deniedReason,
                ],
            );

            if ($deniedReason === 'verified_email_required') {
                return redirect()
                    ->route('verification.notice')
                    ->with('status', 'verification-required-for-protected-features');
            }

            abort(403);
        }

        $access->forceFill([
            'access_granted' => true,
            'completed_at' => CarbonImmutable::now(),
        ])->save();

        if ($download->review_eligible) {
            $this->recordVerification($user, $product, $access);
        }

        $this->auditLogService->record(
            eventType: 'products.download.authorized',
            action: 'authorize_product_download',
            actor: $user,
            auditable: $download,
            metadata: [
                'product_id' => $product->getKey(),
                'download_access_id' => $access->getKey(),
                'download_mode' => $download->download_mode->value,
            ],
        );

        return match ($download->download_mode) {
            ProductDownloadMode::ExternalLink,
            ProductDownloadMode::GithubReleaseLink,
            ProductDownloadMode::AppStoreLink,
            ProductDownloadMode::PlayStoreLink => redirect()->away((string) $download->external_url),
            ProductDownloadMode::ManualRequest => response()->view('products.manual-request', [
                'product' => $product,
                'download' => $download,
                'user' => $user,
            ]),
            ProductDownloadMode::DirectDownload,
            ProductDownloadMode::ProtectedPrivateDownload => $this->streamDownload($download),
        };
    }

    public function primaryDownload(Product $product): ?ProductDownload
    {
        $version = $product->currentPublishedVersion;

        if (! $version) {
            return null;
        }

        return $version->downloads()->orderBy('is_primary', 'desc')->orderBy('sort_order')->first();
    }

    private function recordAttempt(User $user, Product $product, ProductDownload $download): ProductDownloadAccess
    {
        return ProductDownloadAccess::query()->create([
            'product_id' => $product->getKey(),
            'product_version_id' => $download->product_version_id,
            'product_download_id' => $download->getKey(),
            'user_id' => $user->getKey(),
            'access_granted' => false,
            'download_mode' => $download->download_mode,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'attempted_at' => CarbonImmutable::now(),
            'metadata' => [
                'download_label' => $download->label,
            ],
        ]);
    }

    private function recordVerification(User $user, Product $product, ProductDownloadAccess $access): void
    {
        ProductUserVerification::query()->updateOrCreate(
            [
                'product_id' => $product->getKey(),
                'user_id' => $user->getKey(),
                'source' => ProductVerificationSource::Download,
            ],
            [
                'product_download_access_id' => $access->getKey(),
                'verified_by' => null,
                'verified_at' => CarbonImmutable::now(),
                'expires_at' => null,
                'notes' => 'Verified through authorized product download access.',
                'metadata' => [
                    'product_download_access_id' => $access->getKey(),
                ],
            ],
        );
    }

    private function hasVerification(User $user, Product $product): bool
    {
        return ProductUserVerification::query()
            ->active()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->exists();
    }

    private function deniedReason(User $user, Product $product, ProductDownload $download): ?string
    {
        if (! $user->isActiveForAuthentication() || ! $user->hasPermission('downloads.protected.access')) {
            return 'not_authorized';
        }

        if ($download->product_id !== $product->getKey()) {
            return 'download_product_mismatch';
        }

        $version = $product->currentPublishedVersion;

        if (! $version || $download->product_version_id !== $version->getKey()) {
            return 'download_version_mismatch';
        }

        if ($product->visibility === ProductVisibility::Private && ! $user->isInternalStaff() && ! $this->hasVerification($user, $product)) {
            return 'private_product_restricted';
        }

        return match ($download->visibility) {
            ProductDownloadVisibility::Authenticated => null,
            ProductDownloadVisibility::Verified => $user->hasVerifiedEmail() ? null : 'verified_email_required',
            ProductDownloadVisibility::Internal => $user->isInternalStaff() ? null : 'internal_only',
            ProductDownloadVisibility::Entitled => $this->hasVerification($user, $product) ? null : 'entitlement_required',
        };
    }

    private function streamDownload(ProductDownload $download): StreamedResponse
    {
        abort_if(! $download->mediaAsset, 404);

        $asset = $download->mediaAsset;
        abort_unless(Storage::disk($asset->disk)->exists($asset->path), 404);

        return Storage::disk($asset->disk)->download($asset->path, $asset->original_name);
    }
}
