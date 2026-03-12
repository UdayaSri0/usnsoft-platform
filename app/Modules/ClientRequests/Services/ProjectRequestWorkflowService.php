<?php

namespace App\Modules\ClientRequests\Services;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\Workflow\Models\StatusHistory;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

class ProjectRequestWorkflowService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly ProjectRequestNotificationService $notificationService,
    ) {}

    /**
     * @return Collection<int, ProjectRequestStatus>
     */
    public function availableStatusesFor(User $user): Collection
    {
        $query = ProjectRequestStatus::query()->ordered();

        if (! $user->hasPermission('requests.statuses.manage')) {
            $query->where('is_system', true);
        }

        return $query->get();
    }

    public function changeStatus(ProjectRequest $projectRequest, ProjectRequestStatus $toStatus, User $actor, ?string $note = null): ProjectRequest
    {
        if (! $toStatus->is_system && ! $actor->hasPermission('requests.statuses.manage')) {
            throw new AuthorizationException('You are not allowed to apply custom request statuses.');
        }

        return $this->database->transaction(function () use ($actor, $note, $projectRequest, $toStatus): ProjectRequest {
            $fromStatus = $projectRequest->currentStatus;

            if ($fromStatus && $fromStatus->is($toStatus)) {
                return $projectRequest;
            }

            $projectRequest->forceFill([
                'current_status_id' => $toStatus->getKey(),
            ])->save();

            StatusHistory::query()->create([
                'statusable_type' => $projectRequest->getMorphClass(),
                'statusable_id' => $projectRequest->getKey(),
                'from_state' => $fromStatus?->code,
                'to_state' => $toStatus->code,
                'visibility' => $toStatus->visible_to_requester
                    ? ProjectRequestCommentVisibility::RequesterVisible->value
                    : ProjectRequestCommentVisibility::Internal->value,
                'changed_by' => $actor->getKey(),
                'reason' => $note,
                'metadata' => [
                    'from_status_id' => $fromStatus?->getKey(),
                    'from_status_name' => $fromStatus?->name,
                    'to_status_id' => $toStatus->getKey(),
                    'to_status_name' => $toStatus->name,
                    'system_status' => $toStatus->system_status?->value,
                    'is_custom_status' => ! $toStatus->is_system,
                ],
                'changed_at' => CarbonImmutable::now(),
            ]);

            $this->auditLogService->record(
                eventType: 'requests.status.changed',
                action: 'change_project_request_status',
                actor: $actor,
                auditable: $projectRequest,
                oldValues: [
                    'status_id' => $fromStatus?->getKey(),
                    'status_code' => $fromStatus?->code,
                ],
                newValues: [
                    'status_id' => $toStatus->getKey(),
                    'status_code' => $toStatus->code,
                ],
                metadata: [
                    'is_custom_status' => ! $toStatus->is_system,
                    'note' => $note,
                ],
            );

            $this->notificationService->dispatchBusinessEvent('client_requests.status_changed', [
                'project_request_id' => $projectRequest->getKey(),
                'to_status_id' => $toStatus->getKey(),
                'to_status_code' => $toStatus->code,
            ]);

            if ($toStatus->visible_to_requester) {
                $this->notificationService->notifyRequesterOfStatusChange($projectRequest->fresh(['requester']), $toStatus, $actor, $note);
            }

            return $projectRequest->fresh([
                'currentStatus',
                'statusHistories',
            ]);
        });
    }
}
