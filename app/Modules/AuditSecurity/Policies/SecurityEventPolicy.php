<?php

namespace App\Modules\AuditSecurity\Policies;

use App\Models\User;
use App\Modules\AuditSecurity\Models\SecurityEvent;

class SecurityEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security.events.view');
    }

    public function view(User $user, SecurityEvent $event): bool
    {
        if ($event->user_id === $user->getKey()) {
            return $user->hasPermission('security.sessions.viewOwn')
                || $user->hasPermission('security.devices.viewOwn');
        }

        return $this->viewAny($user);
    }
}
