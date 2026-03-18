<?php

namespace App\Modules\IdentityAccess\Services\Admin;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class AccountRoleScopeService
{
    /**
     * @return Collection<int, Role>
     */
    public function creatableRoles(User $actor, bool $internalOnly = false): Collection
    {
        return Role::query()
            ->orderBy('is_internal')
            ->orderBy('display_name')
            ->get()
            ->filter(function (Role $role) use ($actor, $internalOnly): bool {
                if ($internalOnly && ! $role->is_internal) {
                    return false;
                }

                return $this->canCreateWithRole($actor, $role);
            })
            ->values();
    }

    /**
     * @return Collection<int, Role>
     */
    public function editableRoles(User $actor, User $target): Collection
    {
        return Role::query()
            ->orderBy('is_internal')
            ->orderBy('display_name')
            ->get()
            ->filter(fn (Role $role): bool => $this->canAssignRole($actor, $target, $role))
            ->values();
    }

    public function canCreateWithRole(User $actor, Role $role): bool
    {
        if ($role->name === CoreRole::User->value) {
            return $actor->hasPermission('users.create')
                || $actor->hasRole(CoreRole::SuperAdmin);
        }

        if (! $actor->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($role->name === CoreRole::Admin->value) {
            return $actor->hasPermission('users.createAdmin')
                || $actor->hasRole(CoreRole::SuperAdmin);
        }

        if ($role->name === CoreRole::SuperAdmin->value) {
            return $actor->hasPermission('users.createSuperAdmin')
                || $actor->hasRole(CoreRole::SuperAdmin);
        }

        return $role->is_internal
            && ($actor->hasPermission('staff.create') || $actor->hasRole(CoreRole::SuperAdmin));
    }

    public function assertCanCreateWithRole(User $actor, Role $role): void
    {
        if (! $this->canCreateWithRole($actor, $role)) {
            throw new AuthorizationException('You are not allowed to create an account with that role.');
        }
    }

    public function canManageTarget(User $actor, User $target): bool
    {
        return Gate::forUser($actor)->allows('manage', $target);
    }

    public function canAssignRole(User $actor, User $target, Role $role): bool
    {
        return Gate::forUser($actor)->allows('assignRole', [$target, $role]);
    }
}
