<?php

namespace App\Modules\IdentityAccess\Policies;

use App\Models\User;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;

class AccountDeletionRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.viewAny')
            || $user->hasPermission('staff.viewAny');
    }

    public function view(User $user, AccountDeletionRequest $request): bool
    {
        return $request->user_id === $user->getKey()
            || $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('account.requestDeletion');
    }

    public function review(User $user, AccountDeletionRequest $request): bool
    {
        return $user->hasPermission('users.deactivate')
            || $user->hasPermission('staff.deactivate');
    }
}
