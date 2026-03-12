<?php

namespace App\Modules\ClientRequests\Policies;

use App\Models\User;
use App\Modules\ClientRequests\Models\ProjectRequestAttachment;

class ProjectRequestAttachmentPolicy
{
    public function view(User $user, ProjectRequestAttachment $attachment): bool
    {
        $projectRequest = $attachment->projectRequest;

        if (! $user->isActiveForAuthentication()) {
            return false;
        }

        if (
            $attachment->visible_to_requester
            && $projectRequest->user_id === $user->getKey()
            && $user->hasPermission('requests.viewOwn')
            && $user->hasPermission('requests.files.download')
        ) {
            return true;
        }

        return $user->hasPermission('requests.viewAny')
            && $user->hasPermission('requests.files.download');
    }
}
