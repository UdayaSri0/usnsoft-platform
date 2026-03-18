<?php

namespace App\Modules\AuditSecurity\Policies;

use App\Models\User;
use App\Modules\AuditSecurity\Models\UserDevice;

class UserDevicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security.devices.viewAny');
    }

    public function view(User $user, UserDevice $device): bool
    {
        if ($device->user_id === $user->getKey()) {
            return $user->hasPermission('security.devices.viewOwn');
        }

        return $this->viewAny($user);
    }

    public function viewOwnDeviceHistory(User $user, User $target): bool
    {
        return $user->is($target) && $user->hasPermission('security.devices.viewOwn');
    }
}
