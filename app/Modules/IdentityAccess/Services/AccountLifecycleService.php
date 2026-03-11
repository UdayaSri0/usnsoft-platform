<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\AccountStatus;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\IdentityAccess\Enums\AccountDeletionRequestStatus;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;

class AccountLifecycleService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function requestDeletion(User $user, ?string $reason = null): AccountDeletionRequest
    {
        $existingOpenRequest = $user->deletionRequests()
            ->where('status', AccountDeletionRequestStatus::Pending->value)
            ->latest('requested_at')
            ->first();

        if ($existingOpenRequest) {
            return $existingOpenRequest;
        }

        $requestedAt = CarbonImmutable::now();

        $request = $user->deletionRequests()->create([
            'status' => AccountDeletionRequestStatus::Pending,
            'reason' => $reason,
            'requested_at' => $requestedAt,
            'metadata' => [
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ],
        ]);

        $user->forceFill([
            'deletion_requested_at' => $requestedAt,
            'status' => AccountStatus::Active,
        ])->save();

        $this->securityEventService->record(
            SecurityEventType::AccountDeletionRequested,
            $user,
            'info',
            ['request_id' => $request->getKey()],
        );

        $this->auditLogService->record(
            eventType: SecurityEventType::AccountDeletionRequested->value,
            action: 'request_account_deletion',
            actor: $user,
            auditable: $request,
            metadata: ['user_id' => $user->getKey()],
        );

        return $request;
    }

    public function deactivate(
        User $actor,
        User $target,
        ?string $reason = null,
    ): void {
        $target->forceFill([
            'status' => AccountStatus::Deactivated,
            'deactivated_at' => CarbonImmutable::now(),
            'deactivated_by' => $actor->getKey(),
            'deactivation_reason' => $reason,
        ])->save();

        $this->securityEventService->record(SecurityEventType::AccountDeactivated, $target, 'warning', [
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::AccountDeactivated->value,
            action: 'deactivate_account',
            actor: $actor,
            auditable: $target,
            newValues: [
                'status' => AccountStatus::Deactivated->value,
                'reason' => $reason,
            ],
        );
    }

    public function reactivate(User $actor, User $target): void
    {
        $target->forceFill([
            'status' => AccountStatus::Active,
            'deactivated_at' => null,
            'deactivated_by' => null,
            'deactivation_reason' => null,
            'suspended_at' => null,
        ])->save();

        $this->securityEventService->record(SecurityEventType::AccountReactivated, $target, 'info', [
            'actor_id' => $actor->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::AccountReactivated->value,
            action: 'reactivate_account',
            actor: $actor,
            auditable: $target,
            newValues: ['status' => AccountStatus::Active->value],
        );
    }
}
