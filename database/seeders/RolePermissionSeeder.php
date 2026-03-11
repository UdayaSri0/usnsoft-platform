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
                'identity.users.view',
                'identity.users.create',
                'identity.users.update',
                'settings.site.view',
                'cms.pages.view',
                'cms.pages.approve',
                'cms.pages.publish',
                'products.view',
                'products.approve',
                'products.publish',
                'blog.posts.approve',
                'blog.posts.publish',
                'audit.logs.view',
                'security.events.view',
            ],
            CoreRole::Editor->value => [
                'cms.pages.view',
                'cms.pages.create',
                'cms.pages.update',
                'cms.pages.submit',
                'blog.posts.view',
                'blog.posts.create',
                'blog.posts.update',
                'blog.posts.submit',
                'faq.view',
                'faq.manage',
            ],
            CoreRole::ProductManager->value => [
                'products.view',
                'products.create',
                'products.update',
                'products.submit',
                'products.download.manage',
                'services.catalog.manage',
            ],
            CoreRole::SalesManager->value => [
                'client_requests.view',
                'client_requests.manage',
                'client_requests.assign',
                'services.catalog.view',
            ],
            CoreRole::Developer->value => [
                'products.view',
                'products.update',
                'notifications.manage',
            ],
            CoreRole::SupportOperations->value => [
                'client_requests.view',
                'client_requests.manage',
                'careers.applications.manage',
                'security.events.view',
            ],
            CoreRole::User->value => [],
            CoreRole::Guest->value => [],
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
