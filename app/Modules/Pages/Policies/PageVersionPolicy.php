<?php

namespace App\Modules\Pages\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Pages\Models\PageVersion;

class PageVersionPolicy
{
    public function view(User $user, PageVersion $version): bool
    {
        return $user->hasPermission(CmsPermission::PagesView->value);
    }

    public function preview(User $user, PageVersion $version): bool
    {
        return $this->view($user, $version)
            && $user->hasPermission(CmsPermission::PagesPreview->value);
    }

    public function update(User $user, PageVersion $version): bool
    {
        return $user->hasPermission(CmsPermission::PagesUpdate->value)
            && $version->workflow_state !== \App\Enums\ContentWorkflowState::Published;
    }

    public function submitForReview(User $user, PageVersion $version): bool
    {
        return $user->hasPermission(CmsPermission::PagesSubmitReview->value)
            && $this->update($user, $version);
    }

    public function approve(User $user, PageVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesApprove->value);
    }

    public function publish(User $user, PageVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesPublish->value);
    }

    public function schedule(User $user, PageVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesSchedule->value);
    }

    public function archive(User $user, PageVersion $version): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesArchive->value);
    }
}
