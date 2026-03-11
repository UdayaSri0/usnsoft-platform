<?php

namespace App\Modules\AuditSecurity\Policies;

use App\Models\User;
use App\Modules\AuditSecurity\Models\UserSessionHistory;

class UserSessionHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security.logs.view');
    }

    public function view(User $user, UserSessionHistory $history): bool
    {
        if ($history->user_id === $user->getKey()) {
            return $user->hasPermission('security.sessions.viewOwn');
        }

        return $this->viewAny($user);
    }

    public function viewOwnSessionHistory(User $user, User $target): bool
    {
        return $user->is($target) && $user->hasPermission('security.sessions.viewOwn');
    }
}
