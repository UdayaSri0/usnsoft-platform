<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('identity.roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('identity.roles.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('identity.roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('identity.roles.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('identity.roles.manage');
    }
}
