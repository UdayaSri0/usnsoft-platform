<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('identity.users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->hasPermission('identity.users.view') || $user->is($target);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('identity.users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermission('identity.users.update') || $user->is($target);
    }

    public function delete(User $user, User $target): bool
    {
        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return $user->hasPermission('identity.users.delete');
    }

    public function assignRole(User $user, User $target, Role $role): bool
    {
        if (! $user->hasPermission('identity.roles.assign') && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($role->is_internal && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return ! ($role->name === CoreRole::SuperAdmin->value && ! $user->hasRole(CoreRole::SuperAdmin));
    }
}
