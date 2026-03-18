<?php

namespace App\Modules\Comments\Policies;

use App\Models\User;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('comments.viewAny');
    }

    public function moderate(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function moderateState(User $user, Comment $comment, CommentStatus $status): bool
    {
        if (! $this->moderate($user, $comment)) {
            return false;
        }

        if ($status->requiresHiddenContentPermission() && ! $user->hasPermission('moderation.hidden.manage')) {
            return false;
        }

        return true;
    }

    public function viewInternalNotes(User $user, Comment $comment): bool
    {
        return $this->moderate($user, $comment)
            && $user->hasPermission('moderation.notes.view');
    }
}
