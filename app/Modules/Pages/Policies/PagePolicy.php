<?php

namespace App\Modules\Pages\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Pages\Models\Page;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CmsPermission::PagesView->value);
    }

    public function view(User $user, Page $page): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($page->is_home && ! $user->hasPermission(CmsPermission::PagesManageHomepage->value)) {
            return false;
        }

        if ($page->is_system_page && ! $user->hasPermission(CmsPermission::PagesManageSystemPages->value)) {
            return false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(CmsPermission::PagesCreate->value);
    }

    public function update(User $user, Page $page): bool
    {
        if (! $user->hasPermission(CmsPermission::PagesUpdate->value)) {
            return false;
        }

        if ($page->is_home && ! $user->hasPermission(CmsPermission::PagesManageHomepage->value)) {
            return false;
        }

        if ($page->is_system_page && ! $user->hasPermission(CmsPermission::PagesManageSystemPages->value)) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Page $page): bool
    {
        if ($page->is_home || $page->is_system_page) {
            return false;
        }

        return $user->hasPermission(CmsPermission::PagesDelete->value);
    }

    public function compose(User $user, Page $page): bool
    {
        return $this->update($user, $page)
            && $user->hasPermission(CmsPermission::PagesCompose->value);
    }

    public function submitForReview(User $user, Page $page): bool
    {
        return $this->update($user, $page)
            && $user->hasPermission(CmsPermission::PagesSubmitReview->value);
    }

    public function preview(User $user, Page $page): bool
    {
        return $this->view($user, $page)
            && $user->hasPermission(CmsPermission::PagesPreview->value);
    }

    public function approve(User $user, Page $page): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesApprove->value)
            && $this->view($user, $page);
    }

    public function reject(User $user, Page $page): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesReject->value)
            && $this->view($user, $page);
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesPublish->value)
            && $this->view($user, $page);
    }

    public function schedule(User $user, Page $page): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesSchedule->value)
            && $this->view($user, $page);
    }

    public function archive(User $user, Page $page): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::PagesArchive->value)
            && $this->view($user, $page);
    }
}
