<?php

namespace App\Modules\Products\Services;

use App\Models\User;
use App\Modules\Products\Models\ProductPreviewAccessToken;
use App\Modules\Products\Models\ProductVersion;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class ProductPreviewTokenService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function issue(ProductVersion $version, ?User $actor = null): string
    {
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        $previewToken = ProductPreviewAccessToken::query()->create([
            'product_version_id' => $version->getKey(),
            'token_hash' => $tokenHash,
            'generated_by' => $actor?->getKey(),
            'expires_at' => CarbonImmutable::now()->addMinutes((int) config('cms.preview.ttl_minutes', 30)),
            'created_at' => CarbonImmutable::now(),
        ]);

        $version->forceFill([
            'preview_token_hash' => $tokenHash,
        ])->save();

        $this->auditLogService->record(
            eventType: 'products.preview.generated',
            action: 'generate_product_preview_token',
            actor: $actor,
            auditable: $version,
            metadata: [
                'product_preview_access_token_id' => $previewToken->getKey(),
                'expires_at' => optional($previewToken->expires_at)->toIso8601String(),
            ],
        );

        return $plainToken;
    }

    public function verify(ProductVersion $version, string $plainToken, ?User $actor = null): bool
    {
        $tokenHash = hash('sha256', $plainToken);

        $token = ProductPreviewAccessToken::query()
            ->where('product_version_id', $version->getKey())
            ->where('token_hash', $tokenHash)
            ->latest('created_at')
            ->first();

        if (! $token || $token->isExpired()) {
            return false;
        }

        $token->forceFill([
            'last_accessed_at' => CarbonImmutable::now(),
        ])->save();

        $this->auditLogService->record(
            eventType: 'products.preview.accessed',
            action: 'access_product_preview',
            actor: $actor,
            auditable: $version,
            metadata: [
                'product_preview_access_token_id' => $token->getKey(),
            ],
        );

        return true;
    }
}
