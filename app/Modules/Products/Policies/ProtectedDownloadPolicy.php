<?php

namespace App\Modules\Products\Policies;

use App\Models\User;

class ProtectedDownloadPolicy
{
    public function access(User $user): bool
    {
        return $user->hasVerifiedEmail()
            && $user->isActiveForAuthentication()
            && $user->hasPermission('downloads.protected.access');
    }
}
