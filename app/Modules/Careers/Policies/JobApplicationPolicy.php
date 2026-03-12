<?php

namespace App\Modules\Careers\Policies;

use App\Models\User;
use App\Modules\Careers\Enums\CareerPermission;
use App\Modules\Careers\Models\JobApplication;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CareerPermission::ApplicationsView->value);
    }

    public function view(User $user, JobApplication $application): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, JobApplication $application): bool
    {
        return $user->hasPermission(CareerPermission::ApplicationsUpdate->value);
    }

    public function addNote(User $user, JobApplication $application): bool
    {
        return $user->hasPermission(CareerPermission::ApplicationsNotesManage->value);
    }

    public function downloadFile(User $user, JobApplication $application): bool
    {
        return $user->hasPermission(CareerPermission::ApplicationsFilesView->value);
    }
}
