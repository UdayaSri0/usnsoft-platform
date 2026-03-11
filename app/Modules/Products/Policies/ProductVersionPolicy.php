<?php

namespace App\Modules\Products\Policies;

use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Products\Enums\ProductPermission;
use App\Modules\Products\Models\ProductVersion;

class ProductVersionPolicy
{
    public function view(User $user, ProductVersion $version): bool
    {
        return $user->hasPermission(ProductPermission::View->value);
    }

    public function update(User $user, ProductVersion $version): bool
    {
        return $user->hasPermission(ProductPermission::Update->value)
            && $version->workflow_state === ContentWorkflowState::Draft;
    }

    public function preview(User $user, ProductVersion $version): bool
    {
        return $this->view($user, $version)
            && $user->hasPermission(ProductPermission::Preview->value);
    }

    public function submitForReview(User $user, ProductVersion $version): bool
    {
        return $this->update($user, $version)
            && $user->hasPermission(ProductPermission::SubmitReview->value);
    }

    public function approve(User $user, ProductVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Approve->value);
    }

    public function reject(User $user, ProductVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Reject->value);
    }

    public function publish(User $user, ProductVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Publish->value);
    }

    public function schedule(User $user, ProductVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Schedule->value);
    }

    public function archive(User $user, ProductVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Archive->value);
    }
}
