<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Models\Role;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

class RolePermissionService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    /**
     * @param  list<int>  $permissionIds
     */
    public function syncPermissions(User $actor, Role $role, array $permissionIds): void
    {
        if (! $actor->hasPermission('users.assignPermissions') && ! $actor->isSuperAdmin()) {
            throw new AuthorizationException('You are not allowed to modify role permissions.');
        }

        if ($role->is_internal && ! $actor->isSuperAdmin()) {
            throw new AuthorizationException('Only SuperAdmin can modify internal role permissions.');
        }

        $previousIds = $role->permissions()->pluck('permissions.id')->all();

        $role->permissions()->sync($permissionIds);

        $this->securityEventService->record(SecurityEventType::PermissionChanged, $actor, 'info', [
            'role_id' => $role->getKey(),
            'permission_ids' => $permissionIds,
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::PermissionChanged->value,
            action: 'sync_role_permissions',
            actor: $actor,
            auditable: $role,
            oldValues: ['permission_ids' => $previousIds],
            newValues: ['permission_ids' => $permissionIds],
            metadata: ['role_id' => $role->getKey()],
        );
    }
}
