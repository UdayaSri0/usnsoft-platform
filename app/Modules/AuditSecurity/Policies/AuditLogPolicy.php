<?php

namespace App\Modules\AuditSecurity\Policies;

use App\Models\User;
use App\Modules\AuditSecurity\Models\AuditLog;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security.logs.view');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $this->viewAny($user);
    }
}
