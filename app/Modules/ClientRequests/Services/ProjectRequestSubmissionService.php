<?php

namespace App\Modules\ClientRequests\Services;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\Workflow\Models\StatusHistory;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class ProjectRequestSubmissionService
{
    public function __construct(
        private readonly ProjectRequestAttachmentService $attachmentService,
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly ProjectRequestNotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, UploadedFile>  $attachments
     */
    public function submit(User $user, array $payload, array $attachments = []): ProjectRequest
    {
        return $this->database->transaction(function () use ($attachments, $payload, $user): ProjectRequest {
            $submittedStatus = ProjectRequestStatus::query()
                ->where('code', ProjectRequestSystemStatus::Submitted->value)
                ->firstOrFail();

            $projectRequest = ProjectRequest::query()->create([
                'user_id' => $user->getKey(),
                'current_status_id' => $submittedStatus->getKey(),
                'requester_name' => trim((string) $payload['requester_name']),
                'company_name' => $this->nullableString($payload['company_name'] ?? null),
                'contact_email' => trim((string) $payload['contact_email']),
                'contact_phone' => $this->nullableString($payload['contact_phone'] ?? null),
                'project_title' => trim((string) $payload['project_title']),
                'project_summary' => trim((string) $payload['project_summary']),
                'project_description' => trim((string) $payload['project_description']),
                'budget' => $payload['budget'] ?? null,
                'deadline' => $payload['deadline'] ?? null,
                'project_type' => $payload['project_type'],
                'requested_features' => $this->normalizeList($payload['requested_features'] ?? null),
                'preferred_tech_stack' => $this->normalizeList($payload['preferred_tech_stack'] ?? null),
                'preferred_meeting_availability' => $this->normalizeList($payload['preferred_meeting_availability'] ?? null),
                'submitted_at' => CarbonImmutable::now(),
            ]);

            StatusHistory::query()->create([
                'statusable_type' => $projectRequest->getMorphClass(),
                'statusable_id' => $projectRequest->getKey(),
                'from_state' => null,
                'to_state' => $submittedStatus->code,
                'visibility' => ProjectRequestCommentVisibility::RequesterVisible->value,
                'changed_by' => $user->getKey(),
                'reason' => 'Request submitted',
                'metadata' => [
                    'to_status_id' => $submittedStatus->getKey(),
                    'to_status_name' => $submittedStatus->name,
                    'system_status' => $submittedStatus->system_status?->value,
                ],
                'changed_at' => CarbonImmutable::now(),
            ]);

            foreach (Arr::wrap($attachments) as $attachment) {
                if ($attachment instanceof UploadedFile) {
                    $this->attachmentService->storeUploadedFile($projectRequest, $attachment, $user);
                }
            }

            $this->auditLogService->record(
                eventType: 'requests.created',
                action: 'create_project_request',
                actor: $user,
                auditable: $projectRequest,
                newValues: [
                    'project_title' => $projectRequest->project_title,
                    'project_type' => $projectRequest->project_type->value,
                    'current_status_id' => $projectRequest->current_status_id,
                ],
                metadata: [
                    'attachments_count' => $projectRequest->attachments()->count(),
                ],
            );

            $this->notificationService->notifySubmitted($projectRequest->fresh(['requester', 'currentStatus']));

            return $projectRequest->fresh([
                'requester',
                'currentStatus',
                'attachments',
                'statusHistories',
            ]);
        });
    }

    /**
     * @return list<string>|null
     */
    private function normalizeList(mixed $value): ?array
    {
        if (! is_scalar($value)) {
            return null;
        }

        $items = collect(preg_split('/\r\n|\r|\n|,/', (string) $value) ?: [])
            ->map(static fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $items !== [] ? $items : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
