<?php

namespace Database\Seeders;

use App\Modules\IdentityAccess\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionScaffoldSeeder extends Seeder
{
    /**
     * @return array<string, list<string>>
     */
    private function permissionGroups(): array
    {
        return [
            'profile' => [
                'profile.view',
                'profile.update',
                'account.requestDeletion',
            ],
            'security' => [
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'security.logs.view',
                'security.events.view',
            ],
            'users' => [
                'users.viewAny',
                'users.view',
                'users.create',
                'users.update',
                'users.deactivate',
                'users.restore',
                'users.assignRoles',
                'users.assignPermissions',
            ],
            'staff' => [
                'staff.create',
                'staff.viewAny',
                'staff.update',
                'staff.deactivate',
            ],
            'rbac' => [
                'roles.view',
                'roles.update',
                'permissions.view',
                'permissions.update',
            ],
            'admin' => [
                'admin.access',
                'superadmin.access',
            ],
            'downloads' => [
                'downloads.protected.access',
            ],
            'requests' => [
                'requests.create',
                'requests.viewOwn',
                'requests.viewAny',
                'requests.updateStatus',
                'requests.commentInternal',
                'requests.commentPublic',
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->permissionGroups() as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::query()->updateOrCreate(
                    ['name' => $permissionName],
                    [
                        'module' => $group,
                        'description' => 'Core permission scaffold',
                        'is_core' => true,
                    ],
                );
            }
        }
    }
}
