<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

class RoleAssignmentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function assign(User $actor, User $target, Role $role): void
    {
        if (! $this->canAssign($actor, $target, $role)) {
            throw new AuthorizationException('You are not allowed to assign this role.');
        }

        $target->assignRole($role, $actor->getKey());

        $this->auditLogService->record(
            eventType: 'identity.role_assigned',
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

        $this->auditLogService->record(
            eventType: 'identity.role_removed',
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
        if (! $actor->hasPermission('identity.roles.assign') && ! $actor->hasRole(CoreRole::SuperAdmin)) {
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
}
