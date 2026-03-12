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
                'requests.files.download',
                'requests.statuses.manage',
                'requests.audit.view',
            ],
            'cms' => [
                'cms.pages.view',
                'cms.pages.create',
                'cms.pages.update',
                'cms.pages.delete',
                'cms.pages.compose',
                'cms.pages.reorder_blocks',
                'cms.pages.use_reusable_blocks',
                'cms.pages.use_advanced_blocks',
                'cms.pages.manage_homepage',
                'cms.pages.manage_system_pages',
                'cms.pages.preview',
                'cms.pages.submit_review',
                'cms.pages.approve',
                'cms.pages.reject',
                'cms.pages.publish',
                'cms.pages.schedule',
                'cms.pages.archive',
                'cms.pages.restore',
                'cms.blocks.view_definitions',
                'cms.blocks.manage_definitions',
                'cms.blocks.manage_reusable',
                'cms.blocks.use_approved_only',
                'cms.seo.manage',
                'cms.audit.view',
                'cms.approvals.view_queue',
                'cms.approvals.act',
                'cms.preview.access_shared',
            ],
            'products' => [
                'products.view',
                'products.create',
                'products.update',
                'products.preview',
                'products.submit_review',
                'products.approve',
                'products.reject',
                'products.publish',
                'products.schedule',
                'products.archive',
                'products.categories.manage',
                'products.tags.manage',
                'products.reviews.moderate',
            ],
            'blog' => [
                'blog.view',
                'blog.create',
                'blog.update',
                'blog.preview',
                'blog.submit_review',
                'blog.approve',
                'blog.reject',
                'blog.publish',
                'blog.schedule',
                'blog.archive',
                'blog.categories.manage',
                'blog.tags.manage',
            ],
            'faq' => [
                'faq.view',
                'faq.create',
                'faq.update',
                'faq.submit_review',
                'faq.approve',
                'faq.reject',
                'faq.publish',
                'faq.schedule',
                'faq.archive',
                'faq.categories.manage',
            ],
            'careers' => [
                'careers.view',
                'careers.create',
                'careers.update',
                'careers.submit_review',
                'careers.approve',
                'careers.reject',
                'careers.publish',
                'careers.schedule',
                'careers.archive',
                'careers.applications.view',
                'careers.applications.update',
                'careers.applications.notes.manage',
                'careers.applications.files.view',
            ],
            'showcase' => [
                'showcase.testimonials.manage',
                'showcase.partners.manage',
                'showcase.team.manage',
                'showcase.timeline.manage',
                'showcase.achievements.manage',
                'showcase.submit_review',
                'showcase.approve',
                'showcase.reject',
                'showcase.publish',
                'showcase.schedule',
                'showcase.archive',
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
