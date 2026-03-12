<?php

namespace App\Modules\Showcase\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Showcase\Enums\ShowcasePermission;
use App\Modules\Showcase\Models\TeamMember;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::TeamManage->value);
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::TeamManage->value);
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->hasPermission(ShowcasePermission::TeamManage->value)
            && $teamMember->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, TeamMember $teamMember): bool
    {
        return $this->update($user, $teamMember)
            && $user->hasPermission(ShowcasePermission::SubmitReview->value);
    }

    public function approve(User $user, TeamMember $teamMember): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Approve->value);
    }

    public function reject(User $user, TeamMember $teamMember): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Reject->value);
    }

    public function publish(User $user, TeamMember $teamMember): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Publish->value);
    }

    public function schedule(User $user, TeamMember $teamMember): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Schedule->value);
    }

    public function archive(User $user, TeamMember $teamMember): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Archive->value);
    }
}
