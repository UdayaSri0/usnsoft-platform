<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\CoreRole;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Models\Role;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;

class RoleAssignmentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function assign(User $actor, User $target, Role $role): void
    {
        if (! $this->canAssign($actor, $target, $role)) {
            throw new AuthorizationException('You are not allowed to assign this role.');
        }

        $target->assignRole($role, $actor->getKey());
        $this->syncMfaRequirement($target);

        $this->securityEventService->record(SecurityEventType::RoleChanged, $target, 'info', [
            'action' => 'assigned',
            'role' => $role->name,
            'actor_id' => $actor->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::RoleChanged->value,
            action: 'assign_role',
            actor: $actor,
            auditable: $target,
            newValues: ['role' => $role->name],
            metadata: [
                'target_user_id' => $target->getKey(),
                'assigned_role_id' => $role->getKey(),
            ],
        );
    }

    public function remove(User $actor, User $target, Role $role): void
    {
        if (! $this->canAssign($actor, $target, $role)) {
            throw new AuthorizationException('You are not allowed to remove this role.');
        }

        $target->removeRole($role);
        $this->syncMfaRequirement($target);

        $this->securityEventService->record(SecurityEventType::RoleChanged, $target, 'warning', [
            'action' => 'removed',
            'role' => $role->name,
            'actor_id' => $actor->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::RoleChanged->value,
            action: 'remove_role',
            actor: $actor,
            auditable: $target,
            oldValues: ['role' => $role->name],
            metadata: [
                'target_user_id' => $target->getKey(),
                'removed_role_id' => $role->getKey(),
            ],
        );
    }

    public function canAssign(User $actor, User $target, Role $role): bool
    {
        if (! $actor->hasPermission('users.assignRoles') && ! $actor->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($role->is_internal && ! $actor->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($role->name === CoreRole::SuperAdmin->value && ! $actor->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($target->hasRole(CoreRole::SuperAdmin) && ! $actor->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return true;
    }

    private function syncMfaRequirement(User $target): void
    {
        $target->refresh();

        $requiresMfa = $target->roles()->where('is_internal', true)->exists();

        $target->forceFill([
            'mfa_required_at' => $requiresMfa
                ? ($target->mfa_required_at ?? CarbonImmutable::now())
                : null,
        ])->save();
    }
}
