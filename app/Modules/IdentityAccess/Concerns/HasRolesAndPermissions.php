<?php

namespace App\Modules\IdentityAccess\Concerns;

use App\Enums\CoreRole;
use App\Modules\IdentityAccess\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRolesAndPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function hasRole(CoreRole|string $role): bool
    {
        $roleName = $role instanceof CoreRole ? $role->value : $role;

        return $this->roles->contains(static fn (Role $assignedRole): bool => $assignedRole->name === $roleName);
    }

    /**
     * @param  list<CoreRole|string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', static fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    public function assignRole(Role|CoreRole|string $role, ?int $assignedBy = null): void
    {
        $roleModel = $this->resolveRole($role);

        $this->roles()->syncWithoutDetaching([
            $roleModel->getKey() => ['assigned_by' => $assignedBy],
        ]);
    }

    public function removeRole(Role|CoreRole|string $role): void
    {
        $roleModel = $this->resolveRole($role);

        $this->roles()->detach($roleModel->getKey());
    }

    private function resolveRole(Role|CoreRole|string $role): Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        $roleName = $role instanceof CoreRole ? $role->value : $role;

        return Role::query()->where('name', $roleName)->firstOrFail();
    }
}
