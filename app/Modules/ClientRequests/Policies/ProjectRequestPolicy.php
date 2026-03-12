<?php

namespace App\Modules\ClientRequests\Policies;

use App\Models\User;
use App\Modules\ClientRequests\Models\ProjectRequest;

class ProjectRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->isActiveForAuthentication()
            && $user->hasVerifiedEmail()
            && $user->hasPermission('requests.create');
    }

    public function viewAny(User $user): bool
    {
        return $user->isActiveForAuthentication()
            && $user->hasPermission('requests.viewAny');
    }

    public function view(User $user, ProjectRequest $projectRequest): bool
    {
        if ($this->viewAny($user)) {
            return true;
        }

        return $user->isActiveForAuthentication()
            && $user->hasPermission('requests.viewOwn')
            && $projectRequest->user_id === $user->getKey();
    }

    public function createPublicComment(User $user, ProjectRequest $projectRequest): bool
    {
        if (! $user->isActiveForAuthentication() || ! $user->hasPermission('requests.commentPublic')) {
            return false;
        }

        if ($this->viewAny($user)) {
            return true;
        }

        return $projectRequest->user_id === $user->getKey();
    }

    public function createInternalComment(User $user, ProjectRequest $projectRequest): bool
    {
        return $this->viewAny($user)
            && $user->hasPermission('requests.commentInternal');
    }

    public function updateStatus(User $user, ProjectRequest $projectRequest): bool
    {
        return $this->viewAny($user)
            && $user->hasPermission('requests.updateStatus');
    }

    public function manageStatuses(User $user): bool
    {
        return $user->isActiveForAuthentication()
            && $user->hasPermission('requests.statuses.manage');
    }

    public function viewAudit(User $user, ProjectRequest $projectRequest): bool
    {
        return $this->viewAny($user)
            && $user->hasPermission('requests.audit.view');
    }
}
