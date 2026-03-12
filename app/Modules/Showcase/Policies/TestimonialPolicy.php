<?php

namespace App\Modules\Showcase\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Showcase\Enums\ShowcasePermission;
use App\Modules\Showcase\Models\Testimonial;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::TestimonialsManage->value);
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(ShowcasePermission::TestimonialsManage->value);
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->hasPermission(ShowcasePermission::TestimonialsManage->value)
            && $testimonial->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, Testimonial $testimonial): bool
    {
        return $this->update($user, $testimonial)
            && $user->hasPermission(ShowcasePermission::SubmitReview->value);
    }

    public function approve(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Approve->value);
    }

    public function reject(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Reject->value);
    }

    public function publish(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Publish->value);
    }

    public function schedule(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Schedule->value);
    }

    public function archive(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ShowcasePermission::Archive->value);
    }
}
