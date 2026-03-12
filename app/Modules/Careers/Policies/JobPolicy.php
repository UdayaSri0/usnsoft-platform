<?php

namespace App\Modules\Careers\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Careers\Enums\CareerPermission;
use App\Modules\Careers\Models\Job;

class JobPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CareerPermission::View->value);
    }

    public function view(User $user, Job $job): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(CareerPermission::Create->value);
    }

    public function update(User $user, Job $job): bool
    {
        return $user->hasPermission(CareerPermission::Update->value)
            && $job->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, Job $job): bool
    {
        return $this->update($user, $job)
            && $user->hasPermission(CareerPermission::SubmitReview->value);
    }

    public function approve(User $user, Job $job): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CareerPermission::Approve->value);
    }

    public function reject(User $user, Job $job): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CareerPermission::Reject->value);
    }

    public function publish(User $user, Job $job): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CareerPermission::Publish->value);
    }

    public function schedule(User $user, Job $job): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CareerPermission::Schedule->value);
    }

    public function archive(User $user, Job $job): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CareerPermission::Archive->value);
    }
}
