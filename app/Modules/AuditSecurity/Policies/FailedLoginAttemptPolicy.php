<?php

namespace App\Modules\AuditSecurity\Policies;

use App\Models\User;
use App\Modules\AuditSecurity\Models\FailedLoginAttempt;

class FailedLoginAttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security.failedLogins.view');
    }

    public function view(User $user, FailedLoginAttempt $attempt): bool
    {
        if ($attempt->user_id === $user->getKey()) {
            return $user->hasPermission('security.sessions.viewOwn');
        }

        return $this->viewAny($user);
    }
}
