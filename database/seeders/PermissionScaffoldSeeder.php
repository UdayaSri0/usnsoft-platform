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
            'identity' => [
                'identity.users.view',
                'identity.users.create',
                'identity.users.update',
                'identity.users.delete',
                'identity.roles.view',
                'identity.roles.assign',
                'identity.roles.manage',
                'identity.permissions.view',
                'identity.permissions.manage',
            ],
            'settings' => [
                'settings.site.view',
                'settings.site.update',
                'settings.branding.update',
            ],
            'cms' => [
                'cms.pages.view',
                'cms.pages.create',
                'cms.pages.update',
                'cms.pages.submit',
                'cms.pages.approve',
                'cms.pages.publish',
                'cms.blocks.manage',
            ],
            'products' => [
                'products.view',
                'products.create',
                'products.update',
                'products.submit',
                'products.approve',
                'products.publish',
                'products.download.manage',
            ],
            'blog' => [
                'blog.posts.view',
                'blog.posts.create',
                'blog.posts.update',
                'blog.posts.submit',
                'blog.posts.approve',
                'blog.posts.publish',
            ],
            'services' => [
                'services.catalog.view',
                'services.catalog.manage',
            ],
            'faq' => [
                'faq.view',
                'faq.manage',
            ],
            'showcase' => [
                'showcase.testimonials.manage',
                'showcase.partners.manage',
                'showcase.team.manage',
                'showcase.timeline.manage',
                'showcase.achievements.manage',
            ],
            'client_requests' => [
                'client_requests.view',
                'client_requests.manage',
                'client_requests.assign',
            ],
            'careers' => [
                'careers.positions.view',
                'careers.positions.manage',
                'careers.applications.manage',
            ],
            'audit_security' => [
                'audit.logs.view',
                'security.events.view',
                'security.events.resolve',
            ],
            'notifications' => [
                'notifications.manage',
                'notifications.send',
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
                        'description' => 'Scaffolded permission',
                        'is_core' => true,
                    ],
                );
            }
        }
    }
}
