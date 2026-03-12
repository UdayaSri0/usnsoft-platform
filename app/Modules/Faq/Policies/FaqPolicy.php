<?php

namespace App\Modules\Faq\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Faq\Enums\FaqPermission;
use App\Modules\Faq\Models\Faq;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(FaqPermission::View->value);
    }

    public function view(User $user, Faq $faq): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(FaqPermission::Create->value);
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->hasPermission(FaqPermission::Update->value)
            && $faq->workflow_state === \App\Enums\ContentWorkflowState::Draft;
    }

    public function submitForReview(User $user, Faq $faq): bool
    {
        return $this->update($user, $faq)
            && $user->hasPermission(FaqPermission::SubmitReview->value);
    }

    public function approve(User $user, Faq $faq): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(FaqPermission::Approve->value);
    }

    public function reject(User $user, Faq $faq): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(FaqPermission::Reject->value);
    }

    public function publish(User $user, Faq $faq): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(FaqPermission::Publish->value);
    }

    public function schedule(User $user, Faq $faq): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(FaqPermission::Schedule->value);
    }

    public function archive(User $user, Faq $faq): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(FaqPermission::Archive->value);
    }
}
