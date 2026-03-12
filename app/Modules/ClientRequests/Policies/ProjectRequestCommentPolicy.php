<?php

namespace App\Modules\ClientRequests\Policies;

use App\Models\User;
use App\Modules\ClientRequests\Models\ProjectRequestComment;

class ProjectRequestCommentPolicy
{
    public function changeVisibility(User $user, ProjectRequestComment $comment): bool
    {
        return $user->isActiveForAuthentication()
            && $user->hasPermission('requests.viewAny')
            && $user->hasPermission('requests.commentPublic');
    }
}
