<?php

namespace App\Modules\Showcase\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Showcase\Enums\ShowcasePermission;
use App\Modules\Showcase\Models\Achievement;

class AchievementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::AchievementsManage->value);
    }

    public function view(User $user, Achievement $achievement): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::AchievementsManage->value);
    }

    public function update(User $user, Achievement $achievement): bool
    {
        return $user->hasPermission(ShowcasePermission::AchievementsManage->value)
            && $achievement->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, Achievement $achievement): bool
    {
        return $this->update($user, $achievement)
            && $user->hasPermission(ShowcasePermission::SubmitReview->value);
    }

    public function approve(User $user, Achievement $achievement): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Approve->value);
    }

    public function reject(User $user, Achievement $achievement): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Reject->value);
    }

    public function publish(User $user, Achievement $achievement): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Publish->value);
    }

    public function schedule(User $user, Achievement $achievement): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Schedule->value);
    }

    public function archive(User $user, Achievement $achievement): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Archive->value);
    }
}
