<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\CoreRole;
use App\Models\User;

class StaffMfaService
{
    public function requiresMfa(User $user): bool
    {
        return $user->hasAnyRole(CoreRole::internalRoles());
    }

    public function isCompliant(User $user): bool
    {
        if (! $this->requiresMfa($user)) {
            return true;
        }

        if ($user->mfa_enabled_at !== null) {
            return true;
        }

        return $user->mfaMethods()
            ->whereNotNull('enabled_at')
            ->exists();
    }

    public function shouldEnforceNow(User $user): bool
    {
        return (bool) config('security.enforce_internal_mfa', false)
            && $this->requiresMfa($user)
            && ! $this->isCompliant($user);
    }
}
