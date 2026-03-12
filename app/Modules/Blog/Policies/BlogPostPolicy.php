<?php

namespace App\Modules\Blog\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Blog\Enums\BlogPermission;
use App\Modules\Blog\Models\BlogPost;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(BlogPermission::View->value);
    }

    public function view(User $user, BlogPost $post): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(BlogPermission::Create->value);
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->hasPermission(BlogPermission::Update->value)
            && $post->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, BlogPost $post): bool
    {
        return $this->update($user, $post)
            && $user->hasPermission(BlogPermission::SubmitReview->value);
    }

    public function approve(User $user, BlogPost $post): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(BlogPermission::Approve->value);
    }

    public function reject(User $user, BlogPost $post): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(BlogPermission::Reject->value);
    }

    public function publish(User $user, BlogPost $post): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(BlogPermission::Publish->value);
    }

    public function schedule(User $user, BlogPost $post): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(BlogPermission::Schedule->value);
    }

    public function archive(User $user, BlogPost $post): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(BlogPermission::Archive->value);
    }
}
