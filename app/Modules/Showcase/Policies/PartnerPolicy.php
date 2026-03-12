<?php

namespace App\Modules\Showcase\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Showcase\Enums\ShowcasePermission;
use App\Modules\Showcase\Models\Partner;

class PartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::PartnersManage->value);
    }

    public function view(User $user, Partner $partner): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::PartnersManage->value);
    }

    public function update(User $user, Partner $partner): bool
    {
        return $user->hasPermission(ShowcasePermission::PartnersManage->value)
            && $partner->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, Partner $partner): bool
    {
        return $this->update($user, $partner)
            && $user->hasPermission(ShowcasePermission::SubmitReview->value);
    }

    public function approve(User $user, Partner $partner): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Approve->value);
    }

    public function reject(User $user, Partner $partner): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Reject->value);
    }

    public function publish(User $user, Partner $partner): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Publish->value);
    }

    public function schedule(User $user, Partner $partner): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Schedule->value);
    }

    public function archive(User $user, Partner $partner): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Archive->value);
    }
}
