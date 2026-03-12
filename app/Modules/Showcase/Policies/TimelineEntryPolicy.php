<?php

namespace App\Modules\Showcase\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Showcase\Enums\ShowcasePermission;
use App\Modules\Showcase\Models\TimelineEntry;

class TimelineEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::TimelineManage->value);
    }

    public function view(User $user, TimelineEntry $timelineEntry): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::TimelineManage->value);
    }

    public function update(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasPermission(ShowcasePermission::TimelineManage->value)
            && $timelineEntry->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, TimelineEntry $timelineEntry): bool
    {
        return $this->update($user, $timelineEntry)
            && $user->hasPermission(ShowcasePermission::SubmitReview->value);
    }

    public function approve(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Approve->value);
    }

    public function reject(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Reject->value);
    }

    public function publish(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Publish->value);
    }

    public function schedule(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Schedule->value);
    }

    public function archive(User $user, TimelineEntry $timelineEntry): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Archive->value);
    }
}
