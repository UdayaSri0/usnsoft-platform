<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('roles.update');
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->is_internal && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_core) {
            return false;
        }

        return $user->hasPermission('roles.update');
    }
}
