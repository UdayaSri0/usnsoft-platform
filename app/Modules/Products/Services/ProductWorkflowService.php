<?php

namespace App\Modules\Products\Services;

use App\Enums\ApprovalAction;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Models\User;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductPlatform;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductDownload;
use App\Modules\Products\Models\ProductVersion;
use App\Modules\Products\Models\ProductVersionFaq;
use App\Modules\Products\Models\ProductVersionPlatform;
use App\Modules\Products\Models\ProductVersionScreenshot;
use App\Modules\Workflow\Models\ApprovalRecord;
use App\Modules\Seo\Services\SeoMetaManager;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Audit\AuditLogService;
use App\Services\Publishing\PublishingService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProductWorkflowService
{
    public function __construct(
        private readonly ApprovalWorkflowService $approvalWorkflowService,
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly ProductContentSanitizerService $sanitizer,
        private readonly PublishingService $publishingService,
        private readonly SeoMetaManager $seoMetaManager,
    ) {}

    /**
     * @param  array<string, mixed>  $productAttributes
     * @param  array<string, mixed>  $versionAttributes
     */
    public function createProductWithDraft(User $actor, array $productAttributes, array $versionAttributes): Product
    {
        return $this->database->transaction(function () use ($actor, $productAttributes, $versionAttributes): Product {
            $product = Product::query()->create([
                'uuid' => (string) Str::uuid(),
                'name_current' => $versionAttributes['name'],
                'slug_current' => $versionAttributes['slug'],
                'short_description_current' => $versionAttributes['short_description'] ?? null,
                'product_kind' => $versionAttributes['product_kind'],
                'visibility' => $versionAttributes['product_visibility'],
                'featured_flag' => (bool) ($versionAttributes['featured_flag'] ?? false),
                'current_version_label' => $versionAttributes['current_version'] ?? null,
                'featured_image_media_id' => $versionAttributes['featured_image_media_id'] ?? null,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            $draft = $product->versions()->create($this->versionPayload(
                actor: $actor,
                versionNumber: 1,
                attributes: $versionAttributes,
            ));

            $this->syncVersionRelations($draft, $actor, $versionAttributes);

            $product->forceFill([
                'current_draft_version_id' => $draft->getKey(),
            ])->save();

            $this->auditLogService->record(
                eventType: 'products.created',
                action: 'create_product',
                actor: $actor,
                auditable: $product,
                newValues: [
                    'name' => $product->name_current,
                    'slug' => $product->slug_current,
                    'draft_version_id' => $draft->getKey(),
                ],
            );

            return $product->fresh(['currentDraftVersion']);
        });
    }

    public function ensureDraft(Product $product, User $actor): ProductVersion
    {
        $draft = $product->currentDraftVersion;

        if ($draft && $draft->workflow_state === ContentWorkflowState::Draft) {
            return $draft;
        }

        $source = $product->currentPublishedVersion ?? $product->versions()->latest('version_number')->first();

        if (! $source) {
            throw new InvalidArgumentException('Product has no source version to branch from.');
        }

        return $this->database->transaction(function () use ($actor, $product, $source): ProductVersion {
            $draft = $product->versions()->create([
                'version_number' => $product->versions()->max('version_number') + 1,
                'product_category_id' => $source->product_category_id,
                'name' => $source->name,
                'slug' => $source->slug,
                'product_kind' => $source->product_kind,
                'short_description' => $source->short_description,
                'full_description' => $source->full_description,
                'rich_body' => $source->rich_body,
                'featured_flag' => $source->featured_flag,
                'product_visibility' => $source->product_visibility,
                'download_visibility' => $source->download_visibility,
                'pricing_mode' => $source->pricing_mode,
                'pricing_text' => $source->pricing_text,
                'current_version' => $source->current_version,
                'release_notes' => $source->release_notes,
                'changelog' => $source->changelog,
                'documentation_link' => $source->documentation_link,
                'github_link' => $source->github_link,
                'support_contact' => $source->support_contact,
                'video_url' => $source->video_url,
                'featured_image_media_id' => $source->featured_image_media_id,
                'release_notes_visible' => $source->release_notes_visible,
                'changelog_visible' => $source->changelog_visible,
                'reviews_enabled' => $source->reviews_enabled,
                'review_requires_verification' => $source->review_requires_verification,
                'workflow_state' => ContentWorkflowState::Draft,
                'approval_state' => ApprovalState::Draft,
                'based_on_version_id' => $source->getKey(),
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            $draft->tags()->sync($source->tags()->pluck('product_tags.id')->all());

            foreach ($source->platforms as $platform) {
                $draft->platforms()->create([
                    'platform' => $platform->platform,
                ]);
            }

            foreach ($source->allFaqItems as $faq) {
                $draft->allFaqItems()->create([
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'sort_order' => $faq->sort_order,
                    'is_visible' => $faq->is_visible,
                ]);
            }

            foreach ($source->screenshots as $screenshot) {
                $draft->screenshots()->create([
                    'media_asset_id' => $screenshot->media_asset_id,
                    'caption' => $screenshot->caption,
                    'sort_order' => $screenshot->sort_order,
                ]);
            }

            foreach ($source->downloads as $download) {
                $draft->downloads()->create([
                    'product_id' => $product->getKey(),
                    'label' => $download->label,
                    'description' => $download->description,
                    'version_label' => $download->version_label,
                    'download_mode' => $download->download_mode,
                    'visibility' => $download->visibility,
                    'external_url' => $download->external_url,
                    'media_asset_id' => $download->media_asset_id,
                    'is_primary' => $download->is_primary,
                    'review_eligible' => $download->review_eligible,
                    'sort_order' => $download->sort_order,
                    'notes' => $download->notes,
                    'created_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ]);
            }

            $draft->relatedProducts()->sync($source->relatedProducts()->pluck('products.id')->all());

            if ($source->seoMeta) {
                $this->seoMetaManager->upsert($draft, $source->seoMeta->toArray());
            }

            $product->forceFill([
                'current_draft_version_id' => $draft->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->auditLogService->record(
                eventType: 'products.draft_created',
                action: 'create_product_draft',
                actor: $actor,
                auditable: $draft,
                metadata: [
                    'product_id' => $product->getKey(),
                    'source_version_id' => $source->getKey(),
                ],
            );

            return $draft;
        });
    }

    /**
     * @param  array<string, mixed>  $productAttributes
     */
    public function updateDraftContent(ProductVersion $draft, User $actor, array $productAttributes): ProductVersion
    {
        if ($draft->workflow_state !== ContentWorkflowState::Draft) {
            throw new InvalidArgumentException('Only draft product versions can be edited directly.');
        }

        return $this->database->transaction(function () use ($actor, $draft, $productAttributes): ProductVersion {
            $draft->forceFill($this->versionPayload(
                actor: $actor,
                versionNumber: $draft->version_number,
                attributes: $productAttributes,
                preserveWorkflow: true,
            ))->save();

            $this->syncVersionRelations($draft, $actor, $productAttributes);

            if ($draft->product->current_published_version_id === null) {
                $this->syncProductSnapshot($draft->product, $draft, $actor, false);
            }

            $this->auditLogService->record(
                eventType: 'products.draft_updated',
                action: 'update_product_draft',
                actor: $actor,
                auditable: $draft,
                newValues: [
                    'name' => $draft->name,
                    'slug' => $draft->slug,
                    'current_version' => $draft->current_version,
                ],
            );

            return $draft->fresh([
                'category',
                'tags',
                'platforms',
                'allFaqItems',
                'screenshots.mediaAsset',
                'downloads',
                'relatedProducts',
                'seoMeta',
            ]);
        });
    }

    public function confirmPreview(ProductVersion $version, User $actor): void
    {
        $version->forceFill([
            'preview_confirmed_at' => CarbonImmutable::now(),
            'preview_confirmed_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: 'products.preview_confirmed',
            action: 'confirm_product_preview',
            actor: $actor,
            auditable: $version,
        );
    }

    public function submitForReview(ProductVersion $version, User $actor, ?string $notes = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::Draft) {
            throw new InvalidArgumentException('Only draft product versions can be submitted for review.');
        }

        $this->database->transaction(function () use ($actor, $notes, $version): void {
            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::InReview,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->forceFill([
                'submitted_by' => $actor->getKey(),
                'submitted_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->approvalWorkflowService->submit(
                approvable: $version,
                requestedBy: $actor,
                comment: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $this->recordApprovalAction($version, ApprovalAction::Submit, $actor, $notes, ContentWorkflowState::Draft, ContentWorkflowState::InReview);
        });
    }

    public function approve(ProductVersion $version, User $actor, ?string $notes = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::InReview) {
            throw new InvalidArgumentException('Only in-review product versions can be approved.');
        }

        $pendingRequest = $version->approvalRequests()
            ->where('approval_state', ApprovalState::PendingReview->value)
            ->latest('submitted_at')
            ->first();

        if (! $pendingRequest) {
            throw new InvalidArgumentException('Approval request not found for product version.');
        }

        $this->database->transaction(function () use ($actor, $notes, $pendingRequest, $version): void {
            $this->approvalWorkflowService->review(
                request: $pendingRequest,
                reviewedBy: $actor,
                decision: ApprovalState::Approved,
                comment: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->refresh();

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Approved,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->forceFill([
                'approved_by' => $actor->getKey(),
                'approved_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($version, ApprovalAction::Approve, $actor, $notes, ContentWorkflowState::InReview, ContentWorkflowState::Approved);
        });
    }

    public function reject(ProductVersion $version, User $actor, ?string $notes = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::InReview) {
            throw new InvalidArgumentException('Only in-review product versions can be rejected.');
        }

        $pendingRequest = $version->approvalRequests()
            ->where('approval_state', ApprovalState::PendingReview->value)
            ->latest('submitted_at')
            ->first();

        if (! $pendingRequest) {
            throw new InvalidArgumentException('Approval request not found for product version.');
        }

        $this->database->transaction(function () use ($actor, $notes, $pendingRequest, $version): void {
            $this->approvalWorkflowService->review(
                request: $pendingRequest,
                reviewedBy: $actor,
                decision: ApprovalState::ChangesRequested,
                comment: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->refresh();

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Draft,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->forceFill([
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($version, ApprovalAction::Reject, $actor, $notes, ContentWorkflowState::InReview, ContentWorkflowState::Draft);
        });
    }

    public function schedulePublish(ProductVersion $version, User $actor, CarbonImmutable $publishAt, ?string $notes = null, ?CarbonImmutable $unpublishAt = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::Approved) {
            throw new InvalidArgumentException('Only approved product versions can be scheduled.');
        }

        if ($publishAt->isPast()) {
            throw new InvalidArgumentException('Scheduled publish time must be in the future.');
        }

        if ($unpublishAt !== null && $unpublishAt->lessThanOrEqualTo($publishAt)) {
            throw new InvalidArgumentException('Scheduled unpublish time must be after scheduled publish time.');
        }

        $this->database->transaction(function () use ($actor, $notes, $publishAt, $unpublishAt, $version): void {
            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Scheduled,
                actor: $actor,
                reason: $notes,
                metadata: [
                    'scope' => 'product_version',
                    'scheduled_publish_at' => $publishAt->toIso8601String(),
                    'scheduled_unpublish_at' => $unpublishAt?->toIso8601String(),
                ],
            );

            $version->forceFill([
                'scheduled_publish_at' => $publishAt,
                'scheduled_unpublish_at' => $unpublishAt,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($version, ApprovalAction::Schedule, $actor, $notes, ContentWorkflowState::Approved, ContentWorkflowState::Scheduled, [
                'scheduled_publish_at' => $publishAt->toIso8601String(),
                'scheduled_unpublish_at' => $unpublishAt?->toIso8601String(),
            ]);
        });
    }

    public function publishNow(ProductVersion $version, User $actor, ?string $notes = null): void
    {
        if (! in_array($version->workflow_state, [ContentWorkflowState::Approved, ContentWorkflowState::Scheduled], true)) {
            throw new InvalidArgumentException('Only approved or scheduled product versions can be published.');
        }

        if ($version->getApprovalState() !== ApprovalState::Approved) {
            throw new InvalidArgumentException('Approval is required before publishing.');
        }

        if (! $version->preview_confirmed_at) {
            throw new InvalidArgumentException('Preview confirmation is required before publishing.');
        }

        $this->database->transaction(function () use ($actor, $notes, $version): void {
            $fromState = $version->workflow_state;

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Published,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->forceFill([
                'published_by' => $actor->getKey(),
                'published_at' => CarbonImmutable::now(),
                'scheduled_publish_at' => null,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->syncProductSnapshot($version->product, $version, $actor, true);

            $this->recordApprovalAction($version, ApprovalAction::Publish, $actor, $notes, $fromState, ContentWorkflowState::Published);
        });
    }

    public function archive(ProductVersion $version, User $actor, ?string $notes = null): void
    {
        if (! in_array($version->workflow_state, [ContentWorkflowState::Published, ContentWorkflowState::Scheduled], true)) {
            throw new InvalidArgumentException('Only published or scheduled product versions can be archived.');
        }

        $this->database->transaction(function () use ($actor, $notes, $version): void {
            $fromState = $version->workflow_state;

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Archived,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'product_version'],
            );

            $version->forceFill([
                'archived_by' => $actor->getKey(),
                'archived_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $product = $version->product;

            if ((int) $product->current_published_version_id === (int) $version->getKey()) {
                $product->forceFill([
                    'current_published_version_id' => null,
                    'updated_by' => $actor->getKey(),
                ])->save();
            }

            $this->recordApprovalAction($version, ApprovalAction::Archive, $actor, $notes, $fromState, ContentWorkflowState::Archived);
        });
    }

    /**
     * @return array{published: int, archived: int}
     */
    public function processScheduledTransitions(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $published = 0;
        $archived = 0;

        $dueScheduledVersions = ProductVersion::query()
            ->where('workflow_state', ContentWorkflowState::Scheduled->value)
            ->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', $now)
            ->get();

        foreach ($dueScheduledVersions as $version) {
            $actor = $version->approved_by ? User::query()->find($version->approved_by) : null;

            if (! $actor) {
                continue;
            }

            $this->publishNow($version, $actor, 'Scheduled publish execution');
            $published++;
        }

        $dueUnpublishVersions = ProductVersion::query()
            ->where('workflow_state', ContentWorkflowState::Published->value)
            ->whereNotNull('scheduled_unpublish_at')
            ->where('scheduled_unpublish_at', '<=', $now)
            ->get();

        foreach ($dueUnpublishVersions as $version) {
            $actor = $version->published_by ? User::query()->find($version->published_by) : null;

            if (! $actor) {
                continue;
            }

            $this->archive($version, $actor, 'Scheduled unpublish execution');
            $archived++;
        }

        return [
            'published' => $published,
            'archived' => $archived,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function versionPayload(User $actor, int $versionNumber, array $attributes, bool $preserveWorkflow = false): array
    {
        $payload = [
            'version_number' => $versionNumber,
            'product_category_id' => $attributes['product_category_id'] ?? null,
            'name' => trim((string) $attributes['name']),
            'slug' => trim((string) $attributes['slug']),
            'product_kind' => $attributes['product_kind'],
            'short_description' => $this->sanitizer->sanitizeNullableText($attributes['short_description'] ?? null),
            'full_description' => $this->sanitizer->sanitizeRichText($attributes['full_description'] ?? null),
            'rich_body' => $this->sanitizer->sanitizeRichText($attributes['rich_body'] ?? null),
            'featured_flag' => (bool) ($attributes['featured_flag'] ?? false),
            'product_visibility' => $attributes['product_visibility'],
            'download_visibility' => $attributes['download_visibility'],
            'pricing_mode' => $attributes['pricing_mode'],
            'pricing_text' => $this->sanitizer->sanitizeNullableText($attributes['pricing_text'] ?? null),
            'current_version' => $this->sanitizer->sanitizeNullableText($attributes['current_version'] ?? null),
            'release_notes' => $this->sanitizer->sanitizeRichText($attributes['release_notes'] ?? null),
            'changelog' => $this->sanitizer->sanitizeRichText($attributes['changelog'] ?? null),
            'documentation_link' => $this->sanitizer->sanitizeUrl($attributes['documentation_link'] ?? null),
            'github_link' => $this->sanitizer->sanitizeUrl($attributes['github_link'] ?? null),
            'support_contact' => $this->sanitizer->sanitizeNullableText($attributes['support_contact'] ?? null),
            'video_url' => $this->sanitizer->sanitizeVideoUrl($attributes['video_url'] ?? null),
            'featured_image_media_id' => $attributes['featured_image_media_id'] ?? null,
            'release_notes_visible' => (bool) ($attributes['release_notes_visible'] ?? false),
            'changelog_visible' => (bool) ($attributes['changelog_visible'] ?? false),
            'reviews_enabled' => (bool) ($attributes['reviews_enabled'] ?? true),
            'review_requires_verification' => (bool) ($attributes['review_requires_verification'] ?? true),
            'workflow_state' => $preserveWorkflow ? null : ContentWorkflowState::Draft,
            'approval_state' => $preserveWorkflow ? null : ApprovalState::Draft,
            'change_notes' => $this->sanitizer->sanitizeNullableText($attributes['change_notes'] ?? null),
            'updated_by' => $actor->getKey(),
        ];

        if ($preserveWorkflow) {
            unset($payload['workflow_state'], $payload['approval_state']);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function syncVersionRelations(ProductVersion $version, User $actor, array $attributes): void
    {
        $tagIds = collect(Arr::wrap($attributes['tag_ids'] ?? []))
            ->filter(fn (mixed $id): bool => is_scalar($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $version->tags()->sync($tagIds);

        $version->platforms()->delete();

        foreach (collect(Arr::wrap($attributes['supported_platforms'] ?? []))->unique() as $platform) {
            if (! in_array((string) $platform, ProductPlatform::values(), true)) {
                continue;
            }

            $version->platforms()->create([
                'platform' => $platform,
            ]);
        }

        $relatedIds = collect(Arr::wrap($attributes['related_product_ids'] ?? []))
            ->filter(fn (mixed $id): bool => is_scalar($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === $version->product_id)
            ->unique()
            ->values()
            ->all();

        $version->relatedProducts()->sync($relatedIds);

        $version->allFaqItems()->delete();

        foreach (array_values(Arr::wrap($attributes['faq_items'] ?? [])) as $index => $faq) {
            $question = $this->sanitizer->sanitizeNullableText($faq['question'] ?? null);
            $answer = $this->sanitizer->sanitizeRichText($faq['answer'] ?? null);

            if ($question === null || $answer === null) {
                continue;
            }

            $version->allFaqItems()->create([
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $index + 1,
                'is_visible' => filter_var($faq['is_visible'] ?? true, FILTER_VALIDATE_BOOL),
            ]);
        }

        $version->screenshots()->delete();

        foreach (array_values(Arr::wrap($attributes['screenshots'] ?? [])) as $index => $screenshot) {
            if (! is_scalar($screenshot['media_asset_id'] ?? null) || trim((string) $screenshot['media_asset_id']) === '') {
                continue;
            }

            $version->screenshots()->create([
                'media_asset_id' => (string) $screenshot['media_asset_id'],
                'caption' => $this->sanitizer->sanitizeNullableText($screenshot['caption'] ?? null),
                'sort_order' => $index + 1,
            ]);
        }

        $version->downloads()->delete();

        foreach (array_values(Arr::wrap($attributes['downloads'] ?? [])) as $index => $download) {
            $mode = (string) ($download['download_mode'] ?? '');

            if ($mode === '' || ! in_array($mode, \App\Modules\Products\Enums\ProductDownloadMode::values(), true)) {
                continue;
            }

            $downloadMode = ProductDownloadMode::from($mode);
            $externalUrl = $this->sanitizer->sanitizeUrl($download['external_url'] ?? null);
            $mediaAssetId = is_scalar($download['media_asset_id'] ?? null) ? trim((string) $download['media_asset_id']) : '';

            if ($downloadMode->requiresExternalUrl() && $externalUrl === null) {
                throw ValidationException::withMessages([
                    'downloads' => 'External-style product downloads require a valid external URL.',
                ]);
            }

            if ($downloadMode->requiresMediaAsset() && $mediaAssetId === '') {
                throw ValidationException::withMessages([
                    'downloads' => 'Protected or direct file downloads require a media asset id.',
                ]);
            }

            $version->downloads()->create([
                'product_id' => $version->product_id,
                'label' => $this->sanitizer->sanitizeNullableText($download['label'] ?? null) ?? 'Download',
                'description' => $this->sanitizer->sanitizeNullableText($download['description'] ?? null),
                'version_label' => $this->sanitizer->sanitizeNullableText($download['version_label'] ?? null),
                'download_mode' => $downloadMode,
                'visibility' => $download['visibility'] ?? $version->download_visibility,
                'external_url' => $externalUrl,
                'media_asset_id' => $mediaAssetId !== '' ? $mediaAssetId : null,
                'is_primary' => filter_var($download['is_primary'] ?? false, FILTER_VALIDATE_BOOL),
                'review_eligible' => filter_var($download['review_eligible'] ?? true, FILTER_VALIDATE_BOOL),
                'sort_order' => $index + 1,
                'notes' => $this->sanitizer->sanitizeNullableText($download['notes'] ?? null),
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);
        }

        $seo = is_array($attributes['seo'] ?? null) ? $attributes['seo'] : [];
        $this->seoMetaManager->upsert($version, $seo);
    }

    private function syncProductSnapshot(Product $product, ProductVersion $version, User $actor, bool $markPublished): void
    {
        $payload = [
            'name_current' => $version->name,
            'slug_current' => $version->slug,
            'short_description_current' => $version->short_description,
            'product_kind' => $version->product_kind,
            'visibility' => $version->product_visibility,
            'featured_flag' => $version->featured_flag,
            'current_version_label' => $version->current_version,
            'featured_image_media_id' => $version->featured_image_media_id,
            'updated_by' => $actor->getKey(),
        ];

        if ($markPublished) {
            $payload['current_published_version_id'] = $version->getKey();
        }

        $product->forceFill($payload)->save();
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function recordApprovalAction(ProductVersion $version, ApprovalAction $action, User $actor, ?string $notes, ContentWorkflowState $from, ContentWorkflowState $to, array $metadata = []): void
    {
        ApprovalRecord::query()->create([
            'approvable_type' => $version->getMorphClass(),
            'approvable_id' => $version->getKey(),
            'action' => $action->value,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'actor_id' => $actor->getKey(),
            'notes' => $notes,
            'metadata_json' => $metadata,
            'created_at' => CarbonImmutable::now(),
        ]);
    }
}
