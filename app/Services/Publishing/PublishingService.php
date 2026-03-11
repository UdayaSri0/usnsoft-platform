<?php

namespace App\Services\Publishing;

use App\Contracts\Publishing\Publishable;
use App\Enums\ContentWorkflowState;
use App\Models\User;
use App\Modules\Workflow\Models\StatusHistory;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class PublishingService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  Model&Publishable  $publishable
     * @param  array<string, mixed>  $metadata
     */
    public function transition(
        Model $publishable,
        ContentWorkflowState $nextState,
        ?User $actor = null,
        ?string $reason = null,
        array $metadata = [],
    ): void {
        $currentState = $publishable->getWorkflowState() ?? ContentWorkflowState::Draft;

        if (! $currentState->canTransitionTo($nextState)) {
            throw new InvalidArgumentException("Invalid content workflow transition from {$currentState->value} to {$nextState->value}.");
        }

        $publishable->setWorkflowState($nextState);
        $publishable->save();

        StatusHistory::query()->create([
            'statusable_type' => $publishable->getMorphClass(),
            'statusable_id' => $publishable->getKey(),
            'from_state' => $currentState->value,
            'to_state' => $nextState->value,
            'changed_by' => $actor?->getKey(),
            'reason' => $reason,
            'metadata' => $metadata,
            'changed_at' => CarbonImmutable::now(),
        ]);

        $this->auditLogService->record(
            eventType: 'workflow.state_transitioned',
            action: 'transition_workflow_state',
            actor: $actor,
            auditable: $publishable,
            oldValues: ['workflow_state' => $currentState->value],
            newValues: ['workflow_state' => $nextState->value],
            metadata: $metadata,
        );
    }
}
