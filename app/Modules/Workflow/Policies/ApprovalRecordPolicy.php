<?php

namespace App\Modules\Workflow\Policies;

use App\Models\User;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Workflow\Models\ApprovalRecord;

class ApprovalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CmsPermission::ApprovalsViewQueue->value)
            || $user->hasPermission(CmsPermission::ApprovalsAct->value);
    }

    public function view(User $user, ApprovalRecord $approvalRecord): bool
    {
        return $this->viewAny($user);
    }
}
