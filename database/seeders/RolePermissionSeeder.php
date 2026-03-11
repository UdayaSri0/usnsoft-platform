<?php

namespace Database\Seeders;

use App\Enums\CoreRole;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * @return array<string, list<string>>
     */
    private function map(): array
    {
        return [
            CoreRole::SuperAdmin->value => ['*'],
            CoreRole::Admin->value => [
                'admin.access',
                'users.viewAny',
                'users.view',
                'users.update',
                'users.deactivate',
                'users.restore',
                'staff.viewAny',
                'staff.update',
                'staff.deactivate',
                'roles.view',
                'permissions.view',
                'security.logs.view',
                'security.events.view',
                'requests.viewAny',
                'requests.updateStatus',
                'requests.commentInternal',
                'requests.commentPublic',
            ],
            CoreRole::Editor->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'requests.commentPublic',
            ],
            CoreRole::ProductManager->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'requests.viewAny',
                'requests.commentInternal',
            ],
            CoreRole::SalesManager->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'requests.viewAny',
                'requests.updateStatus',
                'requests.commentInternal',
                'requests.commentPublic',
            ],
            CoreRole::Developer->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
            ],
            CoreRole::SupportOperations->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'security.events.view',
                'requests.viewAny',
                'requests.updateStatus',
                'requests.commentInternal',
            ],
            CoreRole::User->value => [
                'profile.view',
                'profile.update',
                'account.requestDeletion',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'downloads.protected.access',
                'requests.create',
                'requests.viewOwn',
                'requests.commentPublic',
            ],
        ];
    }

    public function run(): void
    {
        $allPermissions = Permission::query()->pluck('id', 'name');

        foreach ($this->map() as $roleName => $permissionNames) {
            $role = Role::query()->where('name', $roleName)->first();

            if (! $role) {
                continue;
            }

            if ($permissionNames === ['*']) {
                $role->permissions()->sync($allPermissions->values()->all());

                continue;
            }

            $permissionIds = $allPermissions->only($permissionNames)->values()->all();
            $role->permissions()->sync($permissionIds);
        }
    }
}
