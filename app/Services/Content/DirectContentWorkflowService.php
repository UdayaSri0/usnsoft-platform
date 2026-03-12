<?php

namespace App\Services\Content;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalAction;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Models\User;
use App\Modules\Workflow\Models\ApprovalRecord;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Publishing\PublishingService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DirectContentWorkflowService
{
    public function __construct(
        private readonly ApprovalWorkflowService $approvalWorkflowService,
        private readonly DatabaseManager $database,
        private readonly PublishingService $publishingService,
    ) {}

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function submitForReview(Model $content, User $actor, ?string $notes = null, array $metadata = []): void
    {
        if ($content->getWorkflowState() !== ContentWorkflowState::Draft) {
            throw new InvalidArgumentException('Only draft content can be submitted for review.');
        }

        $scope = $this->scope($content);

        $this->database->transaction(function () use ($actor, $content, $metadata, $notes, $scope): void {
            $this->publishingService->transition(
                publishable: $content,
                nextState: ContentWorkflowState::InReview,
                actor: $actor,
                reason: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->forceFill([
                'submitted_by' => $actor->getKey(),
                'submitted_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->approvalWorkflowService->submit(
                approvable: $content,
                requestedBy: $actor,
                comment: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $this->recordApprovalAction($content, ApprovalAction::Submit, $actor, $notes, ContentWorkflowState::Draft, ContentWorkflowState::InReview, $metadata);
        });
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function approve(Model $content, User $actor, ?string $notes = null, array $metadata = []): void
    {
        if ($content->getWorkflowState() !== ContentWorkflowState::InReview) {
            throw new InvalidArgumentException('Only in-review content can be approved.');
        }

        $pendingRequest = $content->approvalRequests()
            ->where('approval_state', ApprovalState::PendingReview->value)
            ->latest('submitted_at')
            ->first();

        if (! $pendingRequest) {
            throw new InvalidArgumentException('Approval request not found for content.');
        }

        $scope = $this->scope($content);

        $this->database->transaction(function () use ($actor, $content, $metadata, $notes, $pendingRequest, $scope): void {
            $this->approvalWorkflowService->review(
                request: $pendingRequest,
                reviewedBy: $actor,
                decision: ApprovalState::Approved,
                comment: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->refresh();

            $this->publishingService->transition(
                publishable: $content,
                nextState: ContentWorkflowState::Approved,
                actor: $actor,
                reason: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->forceFill([
                'approved_by' => $actor->getKey(),
                'approved_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($content, ApprovalAction::Approve, $actor, $notes, ContentWorkflowState::InReview, ContentWorkflowState::Approved, $metadata);
        });
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function reject(Model $content, User $actor, ?string $notes = null, array $metadata = []): void
    {
        if ($content->getWorkflowState() !== ContentWorkflowState::InReview) {
            throw new InvalidArgumentException('Only in-review content can be rejected.');
        }

        $pendingRequest = $content->approvalRequests()
            ->where('approval_state', ApprovalState::PendingReview->value)
            ->latest('submitted_at')
            ->first();

        if (! $pendingRequest) {
            throw new InvalidArgumentException('Approval request not found for content.');
        }

        $scope = $this->scope($content);

        $this->database->transaction(function () use ($actor, $content, $metadata, $notes, $pendingRequest, $scope): void {
            $this->approvalWorkflowService->review(
                request: $pendingRequest,
                reviewedBy: $actor,
                decision: ApprovalState::ChangesRequested,
                comment: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->refresh();

            $this->publishingService->transition(
                publishable: $content,
                nextState: ContentWorkflowState::Draft,
                actor: $actor,
                reason: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->forceFill([
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($content, ApprovalAction::Reject, $actor, $notes, ContentWorkflowState::InReview, ContentWorkflowState::Draft, $metadata);
        });
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function schedulePublish(
        Model $content,
        User $actor,
        CarbonImmutable $publishAt,
        ?string $notes = null,
        ?CarbonImmutable $unpublishAt = null,
        array $metadata = [],
    ): void {
        if ($content->getWorkflowState() !== ContentWorkflowState::Approved) {
            throw new InvalidArgumentException('Only approved content can be scheduled.');
        }

        if ($publishAt->isPast()) {
            throw new InvalidArgumentException('Scheduled publish time must be in the future.');
        }

        if ($unpublishAt !== null && $unpublishAt->lessThanOrEqualTo($publishAt)) {
            throw new InvalidArgumentException('Scheduled unpublish time must be after scheduled publish time.');
        }

        $scope = $this->scope($content);
        $mergedMetadata = array_merge($metadata, [
            'scope' => $scope,
            'scheduled_publish_at' => $publishAt->toIso8601String(),
            'scheduled_unpublish_at' => $unpublishAt?->toIso8601String(),
        ]);

        $this->database->transaction(function () use ($actor, $content, $mergedMetadata, $notes, $publishAt, $unpublishAt): void {
            $this->publishingService->transition(
                publishable: $content,
                nextState: ContentWorkflowState::Scheduled,
                actor: $actor,
                reason: $notes,
                metadata: $mergedMetadata,
            );

            $content->forceFill([
                'scheduled_publish_at' => $publishAt,
                'scheduled_unpublish_at' => $unpublishAt,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($content, ApprovalAction::Schedule, $actor, $notes, ContentWorkflowState::Approved, ContentWorkflowState::Scheduled, $mergedMetadata);
        });
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function publishNow(
        Model $content,
        User $actor,
        ?string $notes = null,
        bool $requirePreviewConfirmation = false,
        array $metadata = [],
    ): void {
        if (! in_array($content->getWorkflowState(), [ContentWorkflowState::Approved, ContentWorkflowState::Scheduled], true)) {
            throw new InvalidArgumentException('Only approved or scheduled content can be published.');
        }

        if ($content->getApprovalState() !== ApprovalState::Approved) {
            throw new InvalidArgumentException('Approval is required before publishing.');
        }

        if ($requirePreviewConfirmation && ! $content->getAttribute('preview_confirmed_at')) {
            throw new InvalidArgumentException('Preview confirmation is required before publishing.');
        }

        $scope = $this->scope($content);

        $this->database->transaction(function () use ($actor, $content, $metadata, $notes, $scope): void {
            $fromState = $content->getWorkflowState() ?? ContentWorkflowState::Approved;

            $this->publishingService->transition(
                publishable: $content,
                nextState: ContentWorkflowState::Published,
                actor: $actor,
                reason: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->forceFill([
                'published_by' => $actor->getKey(),
                'published_at' => CarbonImmutable::now(),
                'scheduled_publish_at' => null,
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($content, ApprovalAction::Publish, $actor, $notes, $fromState, ContentWorkflowState::Published, $metadata);
        });
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function archive(Model $content, User $actor, ?string $notes = null, array $metadata = []): void
    {
        if (! in_array($content->getWorkflowState(), [ContentWorkflowState::Published, ContentWorkflowState::Scheduled], true)) {
            throw new InvalidArgumentException('Only published or scheduled content can be archived.');
        }

        $scope = $this->scope($content);

        $this->database->transaction(function () use ($actor, $content, $metadata, $notes, $scope): void {
            $fromState = $content->getWorkflowState() ?? ContentWorkflowState::Published;

            $this->publishingService->transition(
                publishable: $content,
                nextState: ContentWorkflowState::Archived,
                actor: $actor,
                reason: $notes,
                metadata: array_merge($metadata, ['scope' => $scope]),
            );

            $content->forceFill([
                'archived_by' => $actor->getKey(),
                'archived_at' => CarbonImmutable::now(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->recordApprovalAction($content, ApprovalAction::Archive, $actor, $notes, $fromState, ContentWorkflowState::Archived, $metadata);
        });
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    public function confirmPreview(Model $content, User $actor): void
    {
        $content->forceFill([
            'preview_confirmed_by' => $actor->getKey(),
            'preview_confirmed_at' => CarbonImmutable::now(),
            'updated_by' => $actor->getKey(),
        ])->save();
    }

    /**
     * @param  class-string<Model&Publishable&RequiresApproval>  $modelClass
     * @return array{published: int, archived: int}
     */
    public function processScheduledTransitions(string $modelClass, bool $requirePreviewConfirmation = false, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $published = 0;
        $archived = 0;

        $dueScheduledItems = $modelClass::query()
            ->where('workflow_state', ContentWorkflowState::Scheduled->value)
            ->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', $now)
            ->get();

        foreach ($dueScheduledItems as $item) {
            $actor = $item->approved_by ? User::query()->find($item->approved_by) : null;

            if (! $actor) {
                continue;
            }

            $this->publishNow($item, $actor, 'Scheduled publish execution', $requirePreviewConfirmation);
            $published++;
        }

        $dueUnpublishItems = $modelClass::query()
            ->where('workflow_state', ContentWorkflowState::Published->value)
            ->whereNotNull('scheduled_unpublish_at')
            ->where('scheduled_unpublish_at', '<=', $now)
            ->get();

        foreach ($dueUnpublishItems as $item) {
            $actor = $item->published_by ? User::query()->find($item->published_by) : null;

            if (! $actor) {
                continue;
            }

            $this->archive($item, $actor, 'Scheduled unpublish execution');
            $archived++;
        }

        return [
            'published' => $published,
            'archived' => $archived,
        ];
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     * @param  array<string, mixed>  $metadata
     */
    private function recordApprovalAction(
        Model $content,
        ApprovalAction $action,
        User $actor,
        ?string $notes,
        ContentWorkflowState $from,
        ContentWorkflowState $to,
        array $metadata = [],
    ): void {
        ApprovalRecord::query()->create([
            'approvable_type' => $content->getMorphClass(),
            'approvable_id' => $content->getKey(),
            'action' => $action->value,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'actor_id' => $actor->getKey(),
            'notes' => $notes,
            'metadata_json' => $metadata,
            'created_at' => CarbonImmutable::now(),
        ]);
    }

    /**
     * @param  Model&Publishable&RequiresApproval  $content
     */
    private function scope(Model $content): string
    {
        return str_replace('\\', '.', $content::class);
    }
}
