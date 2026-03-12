<?php

namespace App\Modules\Careers\Policies;

use App\Models\User;
use App\Modules\Careers\Models\JobApplicationFile;

class JobApplicationFilePolicy
{
    public function view(User $user, JobApplicationFile $file): bool
    {
        return $user->can('downloadFile', $file->application);
    }
}
