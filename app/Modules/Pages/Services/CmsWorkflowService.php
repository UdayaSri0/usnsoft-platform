<?php

namespace App\Modules\Pages\Services;

use App\Enums\ApprovalAction;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Pages\Enums\BlockEditorMode;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Models\PageVersionBlock;
use App\Modules\Pages\Models\ReusableBlock;
use App\Modules\Workflow\Models\ApprovalRecord;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Audit\AuditLogService;
use App\Services\Publishing\PublishingService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CmsWorkflowService
{
    public function __construct(
        private readonly ApprovalWorkflowService $approvalWorkflowService,
        private readonly AuditLogService $auditLogService,
        private readonly BlockValidationService $blockValidationService,
        private readonly DatabaseManager $database,
        private readonly PageRenderService $pageRenderService,
        private readonly PublishingService $publishingService,
    ) {}

    /**
     * @param  array<string, mixed>  $pageAttributes
     * @param  array<string, mixed>  $versionAttributes
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function createPageWithDraft(
        User $actor,
        array $pageAttributes,
        array $versionAttributes,
        array $blocks = [],
    ): Page {
        return $this->database->transaction(function () use ($actor, $pageAttributes, $versionAttributes, $blocks): Page {
            $page = Page::query()->create([
                'key' => $pageAttributes['key'] ?? null,
                'page_type' => $pageAttributes['page_type'],
                'title_current' => $versionAttributes['title'],
                'slug_current' => $versionAttributes['slug'],
                'path_current' => $versionAttributes['path'],
                'is_home' => (bool) ($pageAttributes['is_home'] ?? false),
                'is_system_page' => (bool) ($pageAttributes['is_system_page'] ?? false),
                'is_locked_slug' => (bool) ($pageAttributes['is_locked_slug'] ?? false),
                'is_active' => true,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            $draft = $page->versions()->create([
                'version_number' => 1,
                'title' => $versionAttributes['title'],
                'slug' => $versionAttributes['slug'],
                'path' => $versionAttributes['path'],
                'summary' => $versionAttributes['summary'] ?? null,
                'workflow_state' => ContentWorkflowState::Draft,
                'approval_state' => ApprovalState::Draft,
                'change_notes' => $versionAttributes['change_notes'] ?? null,
                'seo_snapshot_json' => is_array($versionAttributes['seo_snapshot_json'] ?? null)
                    ? $versionAttributes['seo_snapshot_json']
                    : null,
                'layout_settings_json' => is_array($versionAttributes['layout_settings_json'] ?? null)
                    ? $versionAttributes['layout_settings_json']
                    : null,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            $this->syncBlocks($draft, $actor, $blocks);

            $page->forceFill([
                'current_draft_version_id' => $draft->getKey(),
            ])->save();

            $this->auditLogService->record(
                eventType: 'cms.page.created',
                action: 'create_page',
                actor: $actor,
                auditable: $page,
                newValues: [
                    'title' => $page->title_current,
                    'path' => $page->path_current,
                    'draft_version_id' => $draft->getKey(),
                ],
            );

            return $page->fresh(['currentDraftVersion']);
        });
    }

    public function ensureDraft(Page $page, User $actor): PageVersion
    {
        $draft = $page->currentDraftVersion;

        if ($draft && $draft->workflow_state === ContentWorkflowState::Draft) {
            return $draft;
        }

        $source = $page->currentPublishedVersion ?? $page->versions()->latest('version_number')->first();

        if (! $source) {
            throw new InvalidArgumentException('Page has no source version to branch from.');
        }

        return $this->database->transaction(function () use ($actor, $page, $source): PageVersion {
            $draft = $page->versions()->create([
                'version_number' => $page->nextVersionNumber(),
                'title' => $source->title,
                'slug' => $source->slug,
                'path' => $source->path,
                'summary' => $source->summary,
                'workflow_state' => ContentWorkflowState::Draft,
                'approval_state' => ApprovalState::Draft,
                'change_notes' => null,
                'seo_snapshot_json' => $source->seo_snapshot_json,
                'layout_settings_json' => $source->layout_settings_json,
                'based_on_version_id' => $source->getKey(),
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            foreach ($source->blocks as $block) {
                $draft->blocks()->create([
                    'block_definition_id' => $block->block_definition_id,
                    'reusable_block_id' => $block->reusable_block_id,
                    'region_key' => $block->region_key,
                    'sort_order' => $block->sort_order,
                    'internal_name' => $block->internal_name,
                    'is_enabled' => $block->is_enabled,
                    'visibility_json' => $block->visibility_json,
                    'layout_json' => $block->layout_json,
                    'data_json' => $block->data_json,
                    'created_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ]);
            }

            $page->forceFill([
                'current_draft_version_id' => $draft->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->auditLogService->record(
                eventType: 'cms.page.draft_created',
                action: 'create_draft_from_published',
                actor: $actor,
                auditable: $draft,
                metadata: [
                    'page_id' => $page->getKey(),
                    'source_version_id' => $source->getKey(),
                ],
            );

            return $draft;
        });
    }

    /**
     * @param  array<string, mixed>  $versionAttributes
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function updateDraftContent(
        PageVersion $draft,
        User $actor,
        array $versionAttributes,
        array $blocks,
    ): PageVersion {
        if ($draft->workflow_state !== ContentWorkflowState::Draft) {
            throw new InvalidArgumentException('Only draft versions can be edited directly.');
        }

        return $this->database->transaction(function () use ($actor, $draft, $versionAttributes, $blocks): PageVersion {
            $draft->forceFill([
                'title' => $versionAttributes['title'],
                'slug' => $versionAttributes['slug'],
                'path' => $versionAttributes['path'],
                'summary' => $versionAttributes['summary'] ?? null,
                'change_notes' => $versionAttributes['change_notes'] ?? null,
                'seo_snapshot_json' => is_array($versionAttributes['seo_snapshot_json'] ?? null)
                    ? $versionAttributes['seo_snapshot_json']
                    : $draft->seo_snapshot_json,
                'layout_settings_json' => is_array($versionAttributes['layout_settings_json'] ?? null)
                    ? $versionAttributes['layout_settings_json']
                    : $draft->layout_settings_json,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->syncBlocks($draft, $actor, $blocks);

            $page = $draft->page;
            $page->forceFill([
                'title_current' => $draft->title,
                'slug_current' => $draft->slug,
                'path_current' => $draft->path,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->auditLogService->record(
                eventType: 'cms.page.draft_updated',
                action: 'update_draft',
                actor: $actor,
                auditable: $draft,
                newValues: [
                    'title' => $draft->title,
                    'path' => $draft->path,
                    'block_count' => $draft->blocks()->count(),
                ],
            );

            return $draft->fresh(['blocks.blockDefinition', 'blocks.reusableBlock']);
        });
    }

    public function confirmPreview(PageVersion $version, User $actor): void
    {
        $version->forceFill([
            'preview_confirmed_at' => CarbonImmutable::now(),
            'preview_confirmed_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: 'cms.page.preview_confirmed',
            action: 'confirm_preview',
            actor: $actor,
            auditable: $version,
        );
    }

    public function submitForReview(PageVersion $version, User $actor, ?string $notes = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::Draft) {
            throw new InvalidArgumentException('Only draft versions can be submitted for review.');
        }

        $this->database->transaction(function () use ($actor, $notes, $version): void {
            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::InReview,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'cms_page_version'],
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
                metadata: ['scope' => 'cms_page_version'],
            );

            $this->recordApprovalAction($version, ApprovalAction::Submit, $actor, $notes, ContentWorkflowState::Draft, ContentWorkflowState::InReview);
        });
    }

    public function approve(PageVersion $version, User $actor, ?string $notes = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::InReview) {
            throw new InvalidArgumentException('Only in-review versions can be approved.');
        }

        $pendingRequest = $version->approvalRequests()
            ->where('approval_state', ApprovalState::PendingReview->value)
            ->latest('submitted_at')
            ->first();

        if (! $pendingRequest) {
            throw new InvalidArgumentException('Approval request not found for version.');
        }

        $this->database->transaction(function () use ($actor, $notes, $pendingRequest, $version): void {
            $this->approvalWorkflowService->review(
                request: $pendingRequest,
                reviewedBy: $actor,
                decision: ApprovalState::Approved,
                comment: $notes,
                metadata: ['scope' => 'cms_page_version'],
            );

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Approved,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'cms_page_version'],
            );

            $version->forceFill([
                'approved_by' => $actor->getKey(),
                'approved_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($version, ApprovalAction::Approve, $actor, $notes, ContentWorkflowState::InReview, ContentWorkflowState::Approved);
        });
    }

    public function reject(PageVersion $version, User $actor, ?string $notes = null): void
    {
        if ($version->workflow_state !== ContentWorkflowState::InReview) {
            throw new InvalidArgumentException('Only in-review versions can be rejected.');
        }

        $pendingRequest = $version->approvalRequests()
            ->where('approval_state', ApprovalState::PendingReview->value)
            ->latest('submitted_at')
            ->first();

        if (! $pendingRequest) {
            throw new InvalidArgumentException('Approval request not found for version.');
        }

        $this->database->transaction(function () use ($actor, $notes, $pendingRequest, $version): void {
            $this->approvalWorkflowService->review(
                request: $pendingRequest,
                reviewedBy: $actor,
                decision: ApprovalState::ChangesRequested,
                comment: $notes,
                metadata: ['scope' => 'cms_page_version'],
            );

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Draft,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'cms_page_version'],
            );

            $version->forceFill([
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($version, ApprovalAction::Reject, $actor, $notes, ContentWorkflowState::InReview, ContentWorkflowState::Draft);
        });
    }

    public function schedulePublish(
        PageVersion $version,
        User $actor,
        CarbonImmutable $publishAt,
        ?string $notes = null,
        ?CarbonImmutable $unpublishAt = null,
    ): void
    {
        if ($version->workflow_state !== ContentWorkflowState::Approved) {
            throw new InvalidArgumentException('Only approved versions can be scheduled.');
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
                    'scope' => 'cms_page_version',
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

    public function publishNow(PageVersion $version, User $actor, ?string $notes = null): void
    {
        if (! in_array($version->workflow_state, [ContentWorkflowState::Approved, ContentWorkflowState::Scheduled], true)) {
            throw new InvalidArgumentException('Only approved or scheduled versions can be published.');
        }

        if ($version->approval_state !== ApprovalState::Approved) {
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
                metadata: ['scope' => 'cms_page_version'],
            );

            $version->forceFill([
                'published_by' => $actor->getKey(),
                'published_at' => CarbonImmutable::now(),
                'scheduled_publish_at' => null,
                'updated_by' => $actor->getKey(),
            ])->save();

            $page = $version->page;
            $page->forceFill([
                'current_published_version_id' => $version->getKey(),
                'title_current' => $version->title,
                'slug_current' => $version->slug,
                'path_current' => $version->path,
                'is_active' => true,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->pageRenderService->flushPageCache($page);

            $this->recordApprovalAction($version, ApprovalAction::Publish, $actor, $notes, $fromState, ContentWorkflowState::Published);
        });
    }

    public function archive(PageVersion $version, User $actor, ?string $notes = null): void
    {
        if (! in_array($version->workflow_state, [ContentWorkflowState::Published, ContentWorkflowState::Scheduled], true)) {
            throw new InvalidArgumentException('Only published or scheduled versions can be archived.');
        }

        $this->database->transaction(function () use ($actor, $notes, $version): void {
            $fromState = $version->workflow_state;

            $this->publishingService->transition(
                publishable: $version,
                nextState: ContentWorkflowState::Archived,
                actor: $actor,
                reason: $notes,
                metadata: ['scope' => 'cms_page_version'],
            );

            $version->forceFill([
                'archived_by' => $actor->getKey(),
                'archived_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $page = $version->page;

            if ((int) $page->current_published_version_id === (int) $version->getKey()) {
                $page->forceFill([
                    'current_published_version_id' => null,
                    'updated_by' => $actor->getKey(),
                ])->save();
            }

            $this->pageRenderService->flushPageCache($page);

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

        $dueScheduledVersions = PageVersion::query()
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

        $dueUnpublishVersions = PageVersion::query()
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
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function syncBlocks(PageVersion $version, User $actor, array $blocks): void
    {
        $version->blocks()->delete();

        foreach (array_values($blocks) as $index => $blockPayload) {
            $definitionKey = (string) Arr::get($blockPayload, 'block_type');
            $definition = \App\Modules\Pages\Models\BlockDefinition::query()->where('key', $definitionKey)->first();

            if (! $definition || ! $definition->is_active) {
                continue;
            }

            if ($definition->editor_mode === BlockEditorMode::SuperAdminOnly && ! $actor->hasRole(CoreRole::SuperAdmin)) {
                throw ValidationException::withMessages([
                    'blocks' => "Block [{$definition->name}] is restricted to SuperAdmin users.",
                ]);
            }

            if (
                $definition->editor_mode === BlockEditorMode::Advanced
                && ! $actor->hasRole(CoreRole::SuperAdmin)
                && ! $actor->hasPermission(CmsPermission::PagesUseAdvancedBlocks->value)
            ) {
                throw ValidationException::withMessages([
                    'blocks' => "You do not have permission to use advanced block [{$definition->name}].",
                ]);
            }

            $reusableBlockId = $blockPayload['reusable_block_id'] ?? null;
            $reusableBlock = null;

            if ($reusableBlockId) {
                $reusableBlock = ReusableBlock::query()->find($reusableBlockId);

                if (! $reusableBlock) {
                    throw ValidationException::withMessages([
                        'blocks' => "Reusable block [{$reusableBlockId}] does not exist.",
                    ]);
                }

                if (! $actor->hasRole(CoreRole::SuperAdmin) && ! $actor->hasPermission(CmsPermission::PagesUseReusableBlocks->value)) {
                    throw ValidationException::withMessages([
                        'blocks' => 'You do not have permission to use reusable blocks.',
                    ]);
                }

                if (
                    ! $actor->hasRole(CoreRole::SuperAdmin)
                    && (
                        $reusableBlock->workflow_state !== ContentWorkflowState::Published
                        || $reusableBlock->approval_state !== ApprovalState::Approved
                    )
                ) {
                    throw ValidationException::withMessages([
                        'blocks' => "Reusable block [{$reusableBlock->name}] is not approved for use yet.",
                    ]);
                }
            }

            $normalizedData = $this->blockValidationService->validateAndNormalize(
                $definition->key,
                is_array($blockPayload['data'] ?? null) ? $blockPayload['data'] : [],
            );

            $layout = is_array($blockPayload['layout'] ?? null) ? $blockPayload['layout'] : [];
            $visibility = is_array($blockPayload['visibility'] ?? null) ? $blockPayload['visibility'] : [];

            PageVersionBlock::query()->create([
                'page_version_id' => $version->getKey(),
                'block_definition_id' => $definition->getKey(),
                'reusable_block_id' => $reusableBlock?->getKey(),
                'region_key' => $blockPayload['region_key'] ?? 'main',
                'sort_order' => (int) ($blockPayload['sort_order'] ?? ($index + 1)),
                'internal_name' => $blockPayload['internal_name'] ?? null,
                'is_enabled' => (bool) ($blockPayload['is_enabled'] ?? true),
                'visibility_json' => $visibility,
                'layout_json' => $layout,
                'data_json' => $normalizedData,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);
        }

        $version->page->touch();
        $this->pageRenderService->flushVersionCache($version);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function recordApprovalAction(
        PageVersion $version,
        ApprovalAction $action,
        User $actor,
        ?string $notes,
        ContentWorkflowState $from,
        ContentWorkflowState $to,
        array $metadata = [],
    ): void {
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
