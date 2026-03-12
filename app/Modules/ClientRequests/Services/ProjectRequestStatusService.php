<?php

namespace App\Modules\ClientRequests\Services;

use App\Models\User;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Str;

class ProjectRequestStatusService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCustomStatus(User $actor, array $attributes): ProjectRequestStatus
    {
        $status = ProjectRequestStatus::query()->create([
            'code' => $this->normalizeCode($attributes['code'] ?? null, (string) $attributes['name']),
            'name' => trim((string) $attributes['name']),
            'is_system' => false,
            'is_default' => false,
            'is_terminal' => (bool) ($attributes['is_terminal'] ?? false),
            'system_status' => $attributes['system_status'],
            'sort_order' => (int) ($attributes['sort_order'] ?? 500),
            'badge_tone' => $this->nullableString($attributes['badge_tone'] ?? null),
            'visible_to_requester' => (bool) ($attributes['visible_to_requester'] ?? false),
            'created_by' => $actor->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'requests.status.created',
            action: 'create_project_request_status',
            actor: $actor,
            auditable: $status,
            newValues: [
                'code' => $status->code,
                'name' => $status->name,
                'system_status' => $status->system_status?->value,
            ],
        );

        return $status;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCustomStatus(ProjectRequestStatus $status, User $actor, array $attributes): ProjectRequestStatus
    {
        $oldValues = [
            'code' => $status->code,
            'name' => $status->name,
            'is_terminal' => $status->is_terminal,
            'system_status' => $status->system_status?->value,
            'badge_tone' => $status->badge_tone,
            'visible_to_requester' => $status->visible_to_requester,
        ];

        $status->forceFill([
            'code' => $this->normalizeCode($attributes['code'] ?? $status->code, (string) ($attributes['name'] ?? $status->name)),
            'name' => trim((string) ($attributes['name'] ?? $status->name)),
            'is_terminal' => (bool) ($attributes['is_terminal'] ?? false),
            'system_status' => $attributes['system_status'] ?? $status->system_status?->value,
            'sort_order' => (int) ($attributes['sort_order'] ?? $status->sort_order),
            'badge_tone' => $this->nullableString($attributes['badge_tone'] ?? $status->badge_tone),
            'visible_to_requester' => (bool) ($attributes['visible_to_requester'] ?? false),
        ])->save();

        $this->auditLogService->record(
            eventType: 'requests.status.updated',
            action: 'update_project_request_status',
            actor: $actor,
            auditable: $status,
            oldValues: $oldValues,
            newValues: [
                'code' => $status->code,
                'name' => $status->name,
                'is_terminal' => $status->is_terminal,
                'system_status' => $status->system_status?->value,
                'badge_tone' => $status->badge_tone,
                'visible_to_requester' => $status->visible_to_requester,
            ],
        );

        return $status;
    }

    private function normalizeCode(mixed $value, string $fallbackName): string
    {
        $normalized = is_scalar($value) ? trim((string) $value) : '';

        if ($normalized === '') {
            $normalized = Str::slug($fallbackName, '_');
        }

        return Str::lower($normalized);
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
