<?php

namespace App\Services\Approval;

use App\Contracts\Approval\RequiresApproval;
use App\Enums\ApprovalState;
use App\Models\User;
use App\Modules\Workflow\Models\ApprovalRequest;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ApprovalWorkflowService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  Model&RequiresApproval  $approvable
     * @param  array<string, mixed>  $metadata
     */
    public function submit(Model $approvable, User $requestedBy, ?string $comment = null, array $metadata = []): ApprovalRequest
    {
        $currentState = $approvable->getApprovalState() ?? ApprovalState::Draft;
        $nextState = ApprovalState::PendingReview;

        if (! $currentState->canTransitionTo($nextState)) {
            throw new InvalidArgumentException("Cannot transition approval state from {$currentState->value} to {$nextState->value}.");
        }

        $approvable->setApprovalState($nextState);
        $approvable->save();

        $request = $approvable->approvalRequests()->create([
            'requested_by' => $requestedBy->getKey(),
            'approval_state' => $nextState,
            'submitted_at' => CarbonImmutable::now(),
            'comment' => $comment,
            'metadata' => $metadata,
        ]);

        $this->auditLogService->record(
            eventType: 'workflow.submitted_for_approval',
            action: 'submit_for_approval',
            actor: $requestedBy,
            auditable: $approvable,
            newValues: ['approval_state' => $nextState->value],
            metadata: ['approval_request_id' => $request->getKey()],
        );

        return $request;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function review(
        ApprovalRequest $request,
        User $reviewedBy,
        ApprovalState $decision,
        ?string $comment = null,
        array $metadata = []
    ): ApprovalRequest {
        $currentState = $request->approval_state;

        if (! $currentState instanceof ApprovalState || ! $currentState->canTransitionTo($decision)) {
            throw new InvalidArgumentException('Invalid approval transition.');
        }

        $request->fill([
            'approval_state' => $decision,
            'reviewed_by' => $reviewedBy->getKey(),
            'reviewed_at' => CarbonImmutable::now(),
            'comment' => $comment,
            'metadata' => array_merge($request->metadata ?? [], $metadata),
        ]);
        $request->save();

        $approvable = $request->approvable;

        if ($approvable instanceof RequiresApproval) {
            $approvable->setApprovalState($decision);
            $approvable->save();
        }

        $this->auditLogService->record(
            eventType: 'workflow.review_completed',
            action: 'review_approval_request',
            actor: $reviewedBy,
            auditable: $request,
            newValues: ['approval_state' => $decision->value],
            metadata: ['approval_request_id' => $request->getKey()],
        );

        return $request;
    }
}
