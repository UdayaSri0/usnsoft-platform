<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.viewAny')
            || $user->hasPermission('staff.viewAny');
    }

    public function view(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return $user->hasPermission('profile.view') || $user->hasPermission('users.view');
        }

        return $user->hasPermission('users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    public function update(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return $user->hasPermission('profile.update')
                || $user->hasPermission('users.update');
        }

        return $user->hasPermission('users.update');
    }

    public function delete(User $user, User $target): bool
    {
        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return $user->hasPermission('users.deactivate');
    }

    public function restore(User $user, User $target): bool
    {
        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return $user->hasPermission('users.restore');
    }

    public function assignRole(User $user, User $target, Role $role): bool
    {
        if (! $user->hasPermission('users.assignRoles') && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($role->name === CoreRole::Admin->value && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($role->is_internal && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return ! ($role->name === CoreRole::SuperAdmin->value && ! $user->hasRole(CoreRole::SuperAdmin));
    }

    public function assignPermissions(User $user): bool
    {
        return $user->hasPermission('users.assignPermissions')
            || $user->hasRole(CoreRole::SuperAdmin);
    }

    public function createStaff(User $user): bool
    {
        return $user->hasPermission('staff.create')
            && $user->hasRole(CoreRole::SuperAdmin);
    }

    public function manage(User $user, User $target): bool
    {
        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($target->isInternalStaff() && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return $user->hasPermission('users.update')
            || $user->hasRole(CoreRole::SuperAdmin);
    }

    public function deactivateManaged(User $user, User $target): bool
    {
        if ($target->hasRole(CoreRole::SuperAdmin) && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        if ($target->isInternalStaff() && ! $user->hasRole(CoreRole::SuperAdmin)) {
            return false;
        }

        return $user->hasPermission('users.deactivate')
            || $user->hasRole(CoreRole::SuperAdmin);
    }

    public function initiatePasswordReset(User $user, User $target): bool
    {
        if (! $this->manage($user, $target)) {
            return false;
        }

        return $user->hasPermission('users.passwordReset')
            || $user->hasRole(CoreRole::SuperAdmin);
    }

    public function updateStaff(User $user, User $target): bool
    {
        if (! $target->isInternalStaff()) {
            return false;
        }

        return $user->hasRole(CoreRole::SuperAdmin)
            || $user->hasPermission('staff.update');
    }

    public function deactivateStaff(User $user, User $target): bool
    {
        if (! $target->isInternalStaff()) {
            return false;
        }

        return $user->hasRole(CoreRole::SuperAdmin)
            || $user->hasPermission('staff.deactivate');
    }
}
