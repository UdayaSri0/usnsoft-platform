<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
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
use App\Modules\Products\Models\ProductTag;
use App\Modules\Products\Models\ProductUserVerification;
use App\Modules\Products\Services\ProductReviewService;
use App\Modules\Products\Services\ProductWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProductCatalogSeeder extends Seeder
{
    public function __construct(
        private readonly ProductReviewService $reviewService,
        private readonly ProductWorkflowService $workflowService,
    ) {}

    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            return;
        }

        $actor = $this->seedActor();

        $categories = collect([
            ['name' => 'Platform Delivery', 'slug' => 'platform-delivery', 'sort_order' => 10],
            ['name' => 'Security Operations', 'slug' => 'security-operations', 'sort_order' => 20],
            ['name' => 'Internal Tooling', 'slug' => 'internal-tooling', 'sort_order' => 30],
            ['name' => 'Open Source', 'slug' => 'open-source', 'sort_order' => 40],
        ])->mapWithKeys(function (array $category) use ($actor): array {
            $model = ProductCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'created_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ],
            );

            return [$model->slug => $model];
        });

        $tags = collect([
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'Security', 'slug' => 'security'],
            ['name' => 'Downloads', 'slug' => 'downloads'],
            ['name' => 'Desktop', 'slug' => 'desktop'],
            ['name' => 'Mobile', 'slug' => 'mobile'],
            ['name' => 'Automation', 'slug' => 'automation'],
            ['name' => 'Open Source', 'slug' => 'open-source'],
        ])->mapWithKeys(function (array $tag) use ($actor): array {
            $model = ProductTag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                [
                    'name' => $tag['name'],
                    'created_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ],
            );

            return [$model->slug => $model];
        });

        $media = [
            'ops-feature' => $this->publicSvgAsset($actor, 'products/ops-guard-feature.svg', 'ops-guard-feature.svg', '#0f5f92', 'Ops Guard'),
            'ops-screen-1' => $this->publicSvgAsset($actor, 'products/ops-guard-screen-1.svg', 'ops-guard-screen-1.svg', '#0f766e', 'Control Center'),
            'ops-screen-2' => $this->publicSvgAsset($actor, 'products/ops-guard-screen-2.svg', 'ops-guard-screen-2.svg', '#1d4ed8', 'Release Insights'),
            'automation-feature' => $this->publicSvgAsset($actor, 'products/delivery-automation-feature.svg', 'delivery-automation-feature.svg', '#0f172a', 'Automation Layer'),
            'mobile-feature' => $this->publicSvgAsset($actor, 'products/client-workspace-mobile.svg', 'client-workspace-mobile.svg', '#9333ea', 'Client Workspace'),
            'internal-feature' => $this->publicSvgAsset($actor, 'products/internal-support-console.svg', 'internal-support-console.svg', '#b45309', 'Support Console'),
            'download-ops' => $this->privateDownloadAsset($actor, 'products/releases/ops-guard-desktop-2.4.1.zip', 'ops-guard-desktop-2.4.1.zip', 'Sample private desktop release'),
            'download-internal' => $this->privateDownloadAsset($actor, 'products/releases/internal-support-console-1.8.0.zip', 'internal-support-console-1.8.0.zip', 'Sample internal support console release'),
        ];

        $opsGuard = $this->upsertPublishedProduct(
            actor: $actor,
            slug: 'ops-guard-desktop',
            versionAttributes: [
                'product_category_id' => $categories['security-operations']->getKey(),
                'name' => 'Ops Guard Desktop',
                'slug' => 'ops-guard-desktop',
                'product_kind' => ProductKind::DesktopApp,
                'short_description' => 'Desktop release channel for secure support tooling, audit visibility, and operator-ready download governance.',
                'full_description' => '<p>Ops Guard Desktop gives operations teams a controlled desktop client for privileged support workflows, incident artifacts, and approved release distribution.</p>',
                'rich_body' => '<p>Use one product record to publish release notes, screenshots, changelog details, documentation links, and gated downloads without exposing direct storage paths.</p><ul><li>Protected download auditing</li><li>Approval-aware publishing</li><li>Verified review eligibility</li></ul>',
                'featured_flag' => true,
                'product_visibility' => ProductVisibility::Public,
                'download_visibility' => ProductDownloadVisibility::Verified,
                'pricing_mode' => ProductPricingMode::ContactSales,
                'pricing_text' => 'Deployment and support plan available on request',
                'current_version' => '2.4.1',
                'release_notes' => '<p>Improved privileged action review prompts and release diagnostics.</p>',
                'changelog' => '<ul><li>Added guarded update delivery</li><li>Expanded audit event coverage</li><li>Refined session investigation tools</li></ul>',
                'documentation_link' => 'https://docs.usnsoft.test/products/ops-guard-desktop',
                'github_link' => 'https://github.com/usnsoft/ops-guard-desktop',
                'support_contact' => 'support@usnsoft.test',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'featured_image_media_id' => $media['ops-feature']->getKey(),
                'release_notes_visible' => true,
                'changelog_visible' => true,
                'reviews_enabled' => true,
                'review_requires_verification' => true,
                'tag_ids' => [$tags['security']->getKey(), $tags['desktop']->getKey(), $tags['downloads']->getKey()],
                'supported_platforms' => [ProductPlatform::Windows->value, ProductPlatform::MacOS->value, ProductPlatform::Linux->value],
                'faq_items' => [
                    ['question' => 'Who can download the desktop package?', 'answer' => '<p>Authenticated, verified users with approved access can use the protected release route.</p>', 'is_visible' => true],
                    ['question' => 'Are release notes version-specific?', 'answer' => '<p>Yes. Release notes and changelog visibility are stored on the product version that gets published.</p>', 'is_visible' => true],
                ],
                'screenshots' => [
                    ['media_asset_id' => $media['ops-screen-1']->getKey(), 'caption' => 'Operations control center'],
                    ['media_asset_id' => $media['ops-screen-2']->getKey(), 'caption' => 'Release diagnostics'],
                ],
                'downloads' => [
                    [
                        'label' => 'Desktop release package',
                        'description' => 'Signed desktop installer package delivered through the protected route.',
                        'version_label' => 'v2.4.1',
                        'download_mode' => ProductDownloadMode::ProtectedPrivateDownload->value,
                        'visibility' => ProductDownloadVisibility::Verified->value,
                        'media_asset_id' => $media['download-ops']->getKey(),
                        'is_primary' => true,
                        'review_eligible' => true,
                        'notes' => 'Protected binary package',
                    ],
                    [
                        'label' => 'Request implementation workshop',
                        'description' => 'Talk through rollout, onboarding, and environment hardening.',
                        'version_label' => 'Advisory',
                        'download_mode' => ProductDownloadMode::ManualRequest->value,
                        'visibility' => ProductDownloadVisibility::Authenticated->value,
                        'is_primary' => false,
                        'review_eligible' => false,
                        'notes' => 'Routes to client-request flow',
                    ],
                ],
                'seo' => [
                    'meta_title' => 'Ops Guard Desktop | USNsoft',
                    'meta_description' => 'Controlled desktop release publishing with protected downloads, audit visibility, and version-aware product operations.',
                    'og_image_media_id' => $media['ops-feature']->getKey(),
                    'schema_type' => 'SoftwareApplication',
                ],
            ],
        );

        $automationLayer = $this->upsertPublishedProduct(
            actor: $actor,
            slug: 'delivery-automation-layer',
            versionAttributes: [
                'product_category_id' => $categories['platform-delivery']->getKey(),
                'name' => 'Delivery Automation Layer',
                'slug' => 'delivery-automation-layer',
                'product_kind' => ProductKind::WebApp,
                'short_description' => 'Release operations for queues, schedules, and publish-state discipline across a single Laravel platform.',
                'full_description' => '<p>Delivery Automation Layer keeps publish scheduling, queue-backed notifications, and release lifecycle management visible for enterprise teams.</p>',
                'rich_body' => '<p>The platform aligns public discovery with internal governance. Teams can publish release-aware product pages without weakening review boundaries.</p>',
                'featured_flag' => true,
                'product_visibility' => ProductVisibility::Public,
                'download_visibility' => ProductDownloadVisibility::Authenticated,
                'pricing_mode' => ProductPricingMode::Custom,
                'pricing_text' => 'Configured per engagement',
                'current_version' => '1.9.0',
                'release_notes' => '<p>Added safer schedule execution and queue observability notes.</p>',
                'changelog' => '<ul><li>Improved release sequencing</li><li>Expanded audit hooks</li><li>Clarified scheduler diagnostics</li></ul>',
                'documentation_link' => 'https://docs.usnsoft.test/products/delivery-automation-layer',
                'github_link' => 'https://github.com/usnsoft/delivery-automation-layer',
                'support_contact' => 'delivery@usnsoft.test',
                'featured_image_media_id' => $media['automation-feature']->getKey(),
                'release_notes_visible' => true,
                'changelog_visible' => true,
                'reviews_enabled' => true,
                'review_requires_verification' => true,
                'tag_ids' => [$tags['automation']->getKey(), $tags['laravel']->getKey()],
                'supported_platforms' => [ProductPlatform::Web->value],
                'faq_items' => [
                    ['question' => 'Does this include payments?', 'answer' => '<p>No. Phase 1 covers structured publishing, versioning, and controlled downloads, not ecommerce checkout.</p>', 'is_visible' => true],
                ],
                'downloads' => [
                    [
                        'label' => 'Developer quickstart',
                        'description' => 'Open the documentation bundle and implementation notes.',
                        'version_label' => 'Docs',
                        'download_mode' => ProductDownloadMode::ExternalLink->value,
                        'visibility' => ProductDownloadVisibility::Authenticated->value,
                        'external_url' => 'https://docs.usnsoft.test/products/delivery-automation-layer/quickstart',
                        'is_primary' => true,
                        'review_eligible' => true,
                    ],
                    [
                        'label' => 'GitHub release',
                        'description' => 'Inspect release assets and source history.',
                        'version_label' => 'v1.9.0',
                        'download_mode' => ProductDownloadMode::GithubReleaseLink->value,
                        'visibility' => ProductDownloadVisibility::Authenticated->value,
                        'external_url' => 'https://github.com/usnsoft/delivery-automation-layer/releases/tag/v1.9.0',
                        'review_eligible' => true,
                    ],
                ],
                'seo' => [
                    'meta_title' => 'Delivery Automation Layer | USNsoft',
                    'meta_description' => 'Queue-aware product publishing, schedules, and release governance for teams that need one secure operating model.',
                    'og_image_media_id' => $media['automation-feature']->getKey(),
                    'schema_type' => 'SoftwareApplication',
                ],
            ],
        );

        $clientWorkspace = $this->upsertPublishedProduct(
            actor: $actor,
            slug: 'client-workspace-mobile',
            versionAttributes: [
                'product_category_id' => $categories['platform-delivery']->getKey(),
                'name' => 'Client Workspace Mobile',
                'slug' => 'client-workspace-mobile',
                'product_kind' => ProductKind::MobileApp,
                'short_description' => 'Mobile client portal for request tracking, protected resources, and customer-visible workflow clarity.',
                'full_description' => '<p>Client Workspace Mobile gives customers a controlled mobile surface for requests, updates, and protected handoff materials.</p>',
                'rich_body' => '<p>The product stays unlisted until rollout approval while still allowing direct-review stakeholders to inspect the detail page.</p>',
                'featured_flag' => false,
                'product_visibility' => ProductVisibility::Unlisted,
                'download_visibility' => ProductDownloadVisibility::Authenticated,
                'pricing_mode' => ProductPricingMode::Subscription,
                'pricing_text' => 'Included in managed client delivery plans',
                'current_version' => '0.9.3-beta',
                'documentation_link' => 'https://docs.usnsoft.test/products/client-workspace-mobile',
                'support_contact' => 'mobility@usnsoft.test',
                'featured_image_media_id' => $media['mobile-feature']->getKey(),
                'reviews_enabled' => true,
                'review_requires_verification' => true,
                'tag_ids' => [$tags['mobile']->getKey()],
                'supported_platforms' => [ProductPlatform::IOS->value, ProductPlatform::Android->value],
                'downloads' => [
                    [
                        'label' => 'iOS TestFlight',
                        'description' => 'Controlled pre-release access for approved client reviewers.',
                        'version_label' => 'Beta',
                        'download_mode' => ProductDownloadMode::AppStoreLink->value,
                        'visibility' => ProductDownloadVisibility::Authenticated->value,
                        'external_url' => 'https://apps.apple.com/',
                        'is_primary' => true,
                        'review_eligible' => true,
                    ],
                    [
                        'label' => 'Android preview',
                        'description' => 'Managed release channel for Android reviewers.',
                        'version_label' => 'Beta',
                        'download_mode' => ProductDownloadMode::PlayStoreLink->value,
                        'visibility' => ProductDownloadVisibility::Authenticated->value,
                        'external_url' => 'https://play.google.com/store',
                        'review_eligible' => true,
                    ],
                ],
                'seo' => [
                    'meta_title' => 'Client Workspace Mobile | USNsoft',
                    'meta_description' => 'Unlisted client portal product for mobile request tracking and protected access workflows.',
                    'og_image_media_id' => $media['mobile-feature']->getKey(),
                    'robots_index' => false,
                    'robots_follow' => false,
                    'schema_type' => 'MobileApplication',
                ],
            ],
        );

        $internalSupportConsole = $this->upsertPublishedProduct(
            actor: $actor,
            slug: 'internal-support-console',
            versionAttributes: [
                'product_category_id' => $categories['internal-tooling']->getKey(),
                'name' => 'Internal Support Console',
                'slug' => 'internal-support-console',
                'product_kind' => ProductKind::InternalTool,
                'short_description' => 'Private operational console for internal staff, protected procedures, and response coordination.',
                'full_description' => '<p>Internal Support Console is not publicly discoverable. Access is reserved for internal operators and approved verification records.</p>',
                'rich_body' => '<p>Use private visibility for operational products that should remain behind explicit authorization even when the slug is known.</p>',
                'featured_flag' => false,
                'product_visibility' => ProductVisibility::Private,
                'download_visibility' => ProductDownloadVisibility::Internal,
                'pricing_mode' => ProductPricingMode::InternalOnly,
                'pricing_text' => 'Internal operations only',
                'current_version' => '1.8.0',
                'support_contact' => 'ops-internal@usnsoft.test',
                'featured_image_media_id' => $media['internal-feature']->getKey(),
                'reviews_enabled' => false,
                'review_requires_verification' => true,
                'tag_ids' => [$tags['automation']->getKey(), $tags['security']->getKey()],
                'supported_platforms' => [ProductPlatform::Web->value],
                'downloads' => [
                    [
                        'label' => 'Internal console package',
                        'description' => 'Protected internal release package.',
                        'version_label' => 'v1.8.0',
                        'download_mode' => ProductDownloadMode::ProtectedPrivateDownload->value,
                        'visibility' => ProductDownloadVisibility::Internal->value,
                        'media_asset_id' => $media['download-internal']->getKey(),
                        'is_primary' => true,
                        'review_eligible' => false,
                    ],
                ],
                'seo' => [
                    'meta_title' => 'Internal Support Console | USNsoft',
                    'meta_description' => 'Private operational tooling for internal support workflows.',
                    'robots_index' => false,
                    'robots_follow' => false,
                    'schema_type' => 'SoftwareApplication',
                ],
            ],
        );

        $opsDraft = $this->workflowService->ensureDraft($opsGuard, $actor);
        $this->workflowService->updateDraftContent($opsDraft, $actor, array_merge($opsDraft->toArray(), [
            'name' => 'Ops Guard Desktop',
            'slug' => 'ops-guard-desktop',
            'product_kind' => ProductKind::DesktopApp->value,
            'product_visibility' => ProductVisibility::Public->value,
            'download_visibility' => ProductDownloadVisibility::Verified->value,
            'pricing_mode' => ProductPricingMode::ContactSales->value,
            'current_version' => '2.5.0-rc1',
            'short_description' => 'Next release candidate awaiting approval before public rollout.',
            'full_description' => '<p>Release candidate preparing for staged rollout.</p>',
            'rich_body' => '<p>This draft shows how a future version can exist without changing the live product page.</p>',
            'release_notes' => '<p>Pending release candidate notes.</p>',
            'changelog' => '<ul><li>Upcoming diagnostics</li></ul>',
            'documentation_link' => 'https://docs.usnsoft.test/products/ops-guard-desktop/rc',
            'github_link' => 'https://github.com/usnsoft/ops-guard-desktop',
            'support_contact' => 'support@usnsoft.test',
            'featured_image_media_id' => $media['ops-feature']->getKey(),
            'release_notes_visible' => true,
            'changelog_visible' => true,
            'reviews_enabled' => true,
            'review_requires_verification' => true,
            'tag_ids' => [$tags['security']->getKey(), $tags['desktop']->getKey(), $tags['downloads']->getKey()],
            'supported_platforms' => [ProductPlatform::Windows->value, ProductPlatform::MacOS->value, ProductPlatform::Linux->value],
            'downloads' => [
                [
                    'label' => 'Release candidate package',
                    'description' => 'Protected release candidate build.',
                    'version_label' => 'v2.5.0-rc1',
                    'download_mode' => ProductDownloadMode::ProtectedPrivateDownload->value,
                    'visibility' => ProductDownloadVisibility::Verified->value,
                    'media_asset_id' => $media['download-ops']->getKey(),
                    'is_primary' => true,
                    'review_eligible' => true,
                ],
            ],
            'faq_items' => [
                ['question' => 'Is this live yet?', 'answer' => '<p>No. This is a draft version pending review.</p>', 'is_visible' => true],
            ],
            'screenshots' => [
                ['media_asset_id' => $media['ops-screen-1']->getKey(), 'caption' => 'Draft control center'],
            ],
            'seo' => [
                'meta_title' => 'Ops Guard Desktop | USNsoft',
                'meta_description' => 'Controlled desktop release publishing with protected downloads, audit visibility, and version-aware product operations.',
                'og_image_media_id' => $media['ops-feature']->getKey(),
                'schema_type' => 'SoftwareApplication',
            ],
        ]));

        $opsGuard->refresh();
        $automationLayer->refresh();
        $clientWorkspace->refresh();
        $internalSupportConsole->refresh();

        $this->seedReviewData($actor, $opsGuard);
        $this->seedReviewData($actor, $automationLayer);
    }

    private function seedActor(): User
    {
        $actor = User::query()->where('email', 'productmanager@usnsoft.test')->first();

        if ($actor) {
            return $actor;
        }

        $role = Role::query()->where('name', CoreRole::ProductManager->value)->first();

        $actor = User::query()->updateOrCreate(
            ['email' => 'product-catalog-bot@usnsoft.test'],
            [
                'name' => 'USNsoft Product Catalog Bot',
                'password' => Hash::make('ChangeMe123!Secure'),
                'email_verified_at' => now(),
                'status' => AccountStatus::Active,
                'is_internal' => true,
            ],
        );

        if ($role) {
            $actor->roles()->syncWithoutDetaching([
                $role->getKey() => ['assigned_by' => $actor->getKey()],
            ]);
        }

        return $actor;
    }

    /**
     * @param  array<string, mixed>  $versionAttributes
     */
    private function upsertPublishedProduct(User $actor, string $slug, array $versionAttributes): Product
    {
        $existing = Product::query()->where('slug_current', $slug)->first();

        if (! $existing) {
            $product = $this->workflowService->createProductWithDraft($actor, [], $versionAttributes);
            $draft = $product->currentDraftVersion()->firstOrFail();
        } else {
            $product = $existing;
            $draft = $this->workflowService->ensureDraft($product, $actor);
            $this->workflowService->updateDraftContent($draft, $actor, $versionAttributes);
            $draft->refresh();
        }

        if ($draft->workflow_state === ContentWorkflowState::Draft) {
            $this->workflowService->submitForReview($draft, $actor, 'Seeded product publication');
            $draft->refresh();
        }

        if ($draft->workflow_state === ContentWorkflowState::InReview) {
            $this->workflowService->approve($draft, $actor, 'Seeded product approval');
            $draft->refresh();
        }

        if (! $draft->preview_confirmed_at) {
            $this->workflowService->confirmPreview($draft, $actor);
            $draft->refresh();
        }

        if ($draft->workflow_state !== ContentWorkflowState::Published) {
            $this->workflowService->publishNow($draft, $actor, 'Seeded product publication');
        }

        return $product->fresh(['currentPublishedVersion']);
    }

    private function publicSvgAsset(User $actor, string $path, string $filename, string $accent, string $label): MediaAsset
    {
        Storage::disk('public')->put($path, $this->svgMarkup($accent, $label));

        return MediaAsset::query()->updateOrCreate(
            ['disk' => 'public', 'path' => $path],
            [
                'filename' => basename($path),
                'original_name' => $filename,
                'extension' => 'svg',
                'mime_type' => 'image/svg+xml',
                'visibility' => 'public',
                'size_bytes' => strlen($this->svgMarkup($accent, $label)),
                'uploaded_by' => $actor->getKey(),
            ],
        );
    }

    private function privateDownloadAsset(User $actor, string $path, string $filename, string $contents): MediaAsset
    {
        Storage::disk('local')->put($path, $contents);

        return MediaAsset::query()->updateOrCreate(
            ['disk' => 'local', 'path' => $path],
            [
                'filename' => basename($path),
                'original_name' => $filename,
                'extension' => 'zip',
                'mime_type' => 'application/zip',
                'visibility' => 'protected',
                'size_bytes' => strlen($contents),
                'uploaded_by' => $actor->getKey(),
            ],
        );
    }

    private function seedReviewData(User $actor, Product $product): void
    {
        $reviewer = User::query()->where('email', 'user@usnsoft.test')->first();

        if (! $reviewer) {
            return;
        }

        $verification = ProductUserVerification::query()->updateOrCreate(
            [
                'product_id' => $product->getKey(),
                'user_id' => $reviewer->getKey(),
                'source' => ProductVerificationSource::AdminVerified,
            ],
            [
                'verified_by' => $actor->getKey(),
                'verified_at' => CarbonImmutable::now(),
                'notes' => 'Seeded local demo verification.',
            ],
        );

        ProductReview::query()->updateOrCreate(
            [
                'product_id' => $product->getKey(),
                'user_id' => $reviewer->getKey(),
            ],
            [
                'product_user_verification_id' => $verification->getKey(),
                'rating' => 5,
                'title' => 'Reliable release experience',
                'body' => 'The publishing workflow and controlled download path feel deliberate, secure, and easy to follow.',
                'moderation_state' => ProductReviewState::Approved,
                'verification_source' => ProductVerificationSource::AdminVerified,
                'submitted_at' => CarbonImmutable::now()->subDays(3),
                'moderated_at' => CarbonImmutable::now()->subDays(2),
                'moderated_by' => $actor->getKey(),
                'published_at' => CarbonImmutable::now()->subDays(2),
            ],
        );

        $this->reviewService->syncAggregates($product->fresh());
    }

    private function svgMarkup(string $accent, string $label): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="900" viewBox="0 0 1600 900" role="img" aria-label="{$label}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#020617" />
      <stop offset="100%" stop-color="{$accent}" />
    </linearGradient>
  </defs>
  <rect width="1600" height="900" rx="48" fill="url(#bg)" />
  <circle cx="1220" cy="220" r="180" fill="rgba(255,255,255,0.08)" />
  <circle cx="280" cy="700" r="220" fill="rgba(255,255,255,0.06)" />
  <rect x="120" y="130" width="620" height="80" rx="18" fill="rgba(255,255,255,0.12)" />
  <rect x="120" y="260" width="980" height="120" rx="26" fill="rgba(255,255,255,0.08)" />
  <rect x="120" y="430" width="420" height="220" rx="30" fill="rgba(255,255,255,0.1)" />
  <rect x="580" y="430" width="360" height="220" rx="30" fill="rgba(255,255,255,0.08)" />
  <rect x="980" y="430" width="500" height="320" rx="30" fill="rgba(255,255,255,0.12)" />
  <text x="120" y="565" font-family="Manrope, Arial, sans-serif" font-size="64" font-weight="700" fill="#ffffff">{$label}</text>
  <text x="120" y="640" font-family="Manrope, Arial, sans-serif" font-size="30" fill="rgba(255,255,255,0.82)">USNsoft product platform preview media</text>
</svg>
SVG;
    }
}
