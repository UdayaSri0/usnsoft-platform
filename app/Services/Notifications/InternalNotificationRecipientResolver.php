<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Support\Collection;

class InternalNotificationRecipientResolver
{
    /**
     * @param  string|array<int, string>  $permissions
     * @return Collection<int, User>
     */
    public function usersWithAnyPermission(string|array $permissions): Collection
    {
        $names = array_values(array_filter((array) $permissions));

        if ($names === []) {
            return collect();
        }

        return User::query()
            ->where('is_internal', true)
            ->whereHas('roles.permissions', static function ($query) use ($names): void {
                $query->whereIn('name', $names);
            })
            ->get()
            ->filter(static fn (User $user): bool => $user->isActiveForAuthentication())
            ->values();
    }
}
