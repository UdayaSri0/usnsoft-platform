<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Models\User;
use App\Modules\IdentityAccess\Models\MfaMethod;

class MfaMethodPolicy
{
    public function view(User $user, MfaMethod $method): bool
    {
        if ($method->user_id === $user->getKey()) {
            return true;
        }

        return $user->hasPermission('security.mfa.view');
    }

    public function manage(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return true;
        }

        return $user->hasPermission('security.mfa.manage');
    }
}
