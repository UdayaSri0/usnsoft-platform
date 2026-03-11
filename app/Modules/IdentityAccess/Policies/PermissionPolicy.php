<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Models\User;
use App\Modules\IdentityAccess\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('permissions.view');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permissions.view');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permissions.update');
    }
}
