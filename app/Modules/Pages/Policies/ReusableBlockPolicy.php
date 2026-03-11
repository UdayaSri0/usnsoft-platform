<?php

namespace App\Modules\Pages\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Pages\Models\ReusableBlock;

class ReusableBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CmsPermission::BlocksManageReusable->value)
            || $user->hasPermission(CmsPermission::PagesUseReusableBlocks->value);
    }

    public function view(User $user, ReusableBlock $reusableBlock): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(CmsPermission::BlocksManageReusable->value);
    }

    public function update(User $user, ReusableBlock $reusableBlock): bool
    {
        if (! $user->hasPermission(CmsPermission::BlocksManageReusable->value)) {
            return false;
        }

        if ($reusableBlock->workflow_state === \App\Enums\ContentWorkflowState::Published && ! $user->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ReusableBlock $reusableBlock): bool
    {
        return $this->update($user, $reusableBlock);
    }

    public function approve(User $user): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::ApprovalsAct->value);
    }
}
