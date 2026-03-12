<?php

namespace App\Modules\ClientRequests\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ProjectRequestNotificationRecipientResolver
{
    /**
     * @return Collection<int, User>
     */
    public function submissionRecipients(): Collection
    {
        $roles = (array) config('client_requests.staff_notification_roles', []);

        return User::query()
            ->whereHas('roles', static function ($query) use ($roles): void {
                $query->whereIn('name', $roles);
            })
            ->get()
            ->filter(static fn (User $user): bool => $user->isActiveForAuthentication())
            ->values();
    }
}
