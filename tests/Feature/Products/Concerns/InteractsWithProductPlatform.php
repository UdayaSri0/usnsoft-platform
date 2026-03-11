<?php

namespace Tests\Feature\Products\Concerns;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Enums\VisibilityState;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductKind;
use App\Modules\Products\Enums\ProductPlatform;
use App\Modules\Products\Enums\ProductPricingMode;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Enums\ProductVerificationSource;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductCategory;
use App\Modules\Products\Models\ProductReview;
use App\Modules\Products\Models\ProductUserVerification;
use App\Modules\Products\Models\ProductVersion;
use App\Modules\Products\Services\ProductReviewService;
use App\Modules\Products\Services\ProductWorkflowService;
use Carbon\CarbonImmutable;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait InteractsWithProductPlatform
{
    protected function seedProductPlatformCore(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }

    protected function makeUserWithRole(CoreRole $role, bool $verified = true): User
    {
        $user = User::factory()->create([
            'email_verified_at' => $verified ? now() : null,
            'status' => AccountStatus::Active->value,
            'is_internal' => in_array($role, CoreRole::internalRoles(), true),
        ]);

        $roleModel = Role::query()->where('name', $role->value)->firstOrFail();
        $user->assignRole($roleModel, $user->getKey());

        return $user->fresh();
    }

    protected function createPublishedProduct(User $actor, array $overrides = [], ?User $approver = null): Product
    {
        $product = $this->createProductWithDraft($actor, $overrides);
        $draft = $product->currentDraftVersion()->firstOrFail();
        $approver ??= $actor;
        $workflow = app(ProductWorkflowService::class);

        $workflow->submitForReview($draft, $actor, 'Test submit');
        $workflow->approve($draft, $approver, 'Test approve');
        $workflow->confirmPreview($draft, $approver);
        $workflow->publishNow($draft, $approver, 'Test publish');

        return $product->fresh(['currentPublishedVersion.downloads.mediaAsset', 'approvedReviews']);
    }

    protected function createScheduledProduct(User $actor, User $approver, CarbonImmutable $publishAt, array $overrides = [], ?CarbonImmutable $unpublishAt = null): Product
    {
        $product = $this->createProductWithDraft($actor, $overrides);
        $draft = $product->currentDraftVersion()->firstOrFail();
        $workflow = app(ProductWorkflowService::class);

        $workflow->submitForReview($draft, $actor, 'Schedule submit');
        $workflow->approve($draft, $approver, 'Schedule approve');
        $workflow->confirmPreview($draft, $approver);
        $workflow->schedulePublish($draft, $approver, $publishAt, 'Schedule product', $unpublishAt);

        return $product->fresh(['currentDraftVersion']);
    }

    protected function createReviewVerification(User $actor, User $user, Product $product, ProductVerificationSource $source = ProductVerificationSource::AdminVerified): ProductUserVerification
    {
        return ProductUserVerification::query()->create([
            'product_id' => $product->getKey(),
            'user_id' => $user->getKey(),
            'source' => $source,
            'verified_by' => $actor->getKey(),
            'verified_at' => CarbonImmutable::now(),
            'notes' => 'Test verification record.',
        ]);
    }

    protected function createReview(Product $product, User $user, ProductReviewState $state, int $rating = 5, ?ProductUserVerification $verification = null, ?User $moderator = null): ProductReview
    {
        $token = Str::lower(Str::random(8));

        $review = ProductReview::query()->create([
            'product_id' => $product->getKey(),
            'user_id' => $user->getKey(),
            'product_user_verification_id' => $verification?->getKey(),
            'rating' => $rating,
            'title' => 'Review '.$token,
            'body' => "This {$state->value} review body {$token} is long enough to pass validation in feature tests.",
            'moderation_state' => $state,
            'verification_source' => $verification?->source,
            'submitted_at' => CarbonImmutable::now()->subHour(),
            'moderated_at' => $state === ProductReviewState::Pending ? null : CarbonImmutable::now()->subMinutes(30),
            'moderated_by' => $moderator?->getKey(),
            'published_at' => $state === ProductReviewState::Approved ? CarbonImmutable::now()->subMinutes(30) : null,
        ]);

        app(ProductReviewService::class)->syncAggregates($product->fresh());

        return $review;
    }

    protected function createProductWithDraft(User $actor, array $overrides = []): Product
    {
        $category = ProductCategory::query()->create([
            'name' => 'Utilities '.Str::random(6),
            'slug' => 'utilities-'.Str::lower(Str::random(8)),
            'is_active' => true,
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ]);

        $downloadAsset = $this->createMediaAsset($actor, 'local', 'products/releases/'.Str::uuid().'.zip', 'test-release.zip', 'application/zip');

        $payload = [
            'product_category_id' => $category->getKey(),
            'name' => 'Product '.Str::random(8),
            'slug' => 'product-'.Str::lower(Str::random(10)),
            'product_kind' => ProductKind::WebApp->value,
            'short_description' => 'Short description for the product under test.',
            'full_description' => '<p>Publicly renderable product description.</p>',
            'rich_body' => '<p>Extended product body for workflow testing.</p>',
            'featured_flag' => false,
            'product_visibility' => ProductVisibility::Public->value,
            'download_visibility' => ProductDownloadVisibility::Verified->value,
            'pricing_mode' => ProductPricingMode::Free->value,
            'pricing_text' => 'Free',
            'current_version' => '1.0.0',
            'release_notes' => '<p>Release notes.</p>',
            'changelog' => '<ul><li>Initial release</li></ul>',
            'documentation_link' => 'https://docs.example.test/product',
            'github_link' => 'https://github.com/example/product',
            'support_contact' => 'support@example.test',
            'release_notes_visible' => true,
            'changelog_visible' => true,
            'reviews_enabled' => true,
            'review_requires_verification' => true,
            'supported_platforms' => [ProductPlatform::Web->value],
            'faq_items' => [
                [
                    'question' => 'Is this tested?',
                    'answer' => '<p>Yes.</p>',
                    'is_visible' => true,
                ],
            ],
            'downloads' => [
                [
                    'label' => 'Protected release',
                    'description' => 'Protected download package.',
                    'version_label' => 'v1.0.0',
                    'download_mode' => ProductDownloadMode::ProtectedPrivateDownload->value,
                    'visibility' => ProductDownloadVisibility::Verified->value,
                    'media_asset_id' => $downloadAsset->getKey(),
                    'is_primary' => true,
                    'review_eligible' => true,
                    'notes' => 'Feature test package',
                ],
            ],
            'seo' => [
                'meta_title' => 'Test Product',
                'meta_description' => 'Test product metadata.',
                'schema_type' => 'SoftwareApplication',
            ],
        ];

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        return app(ProductWorkflowService::class)->createProductWithDraft($actor, [], $payload);
    }

    protected function createMediaAsset(
        User $actor,
        string $disk = 'local',
        ?string $path = null,
        string $originalName = 'test-release.zip',
        string $mimeType = 'application/zip'
    ): MediaAsset {
        $path ??= 'products/'.Str::uuid().'.zip';
        Storage::disk($disk)->put($path, 'product-platform-test-file');

        return MediaAsset::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => basename($path),
            'original_name' => $originalName,
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'mime_type' => $mimeType,
            'visibility' => $disk === 'public' ? VisibilityState::Public : VisibilityState::Protected,
            'size_bytes' => strlen('product-platform-test-file'),
            'uploaded_by' => $actor->getKey(),
        ]);
    }
}
