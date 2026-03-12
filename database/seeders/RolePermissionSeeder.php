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
                'requests.files.download',
                'requests.statuses.manage',
                'requests.audit.view',
                'downloads.protected.access',
                'cms.pages.view',
                'cms.pages.create',
                'cms.pages.update',
                'cms.pages.compose',
                'cms.pages.reorder_blocks',
                'cms.pages.use_reusable_blocks',
                'cms.pages.manage_homepage',
                'cms.pages.manage_system_pages',
                'cms.pages.preview',
                'cms.pages.submit_review',
                'cms.blocks.view_definitions',
                'cms.blocks.manage_reusable',
                'cms.blocks.use_approved_only',
                'cms.seo.manage',
                'cms.audit.view',
                'cms.approvals.view_queue',
                'products.view',
                'products.create',
                'products.update',
                'products.preview',
                'products.submit_review',
                'products.categories.manage',
                'products.tags.manage',
                'products.reviews.moderate',
                'blog.view',
                'blog.create',
                'blog.update',
                'blog.preview',
                'blog.submit_review',
                'blog.categories.manage',
                'blog.tags.manage',
                'faq.view',
                'faq.create',
                'faq.update',
                'faq.submit_review',
                'faq.categories.manage',
                'careers.view',
                'careers.create',
                'careers.update',
                'careers.submit_review',
                'careers.applications.view',
                'careers.applications.update',
                'careers.applications.notes.manage',
                'careers.applications.files.view',
                'showcase.testimonials.manage',
                'showcase.partners.manage',
                'showcase.team.manage',
                'showcase.timeline.manage',
                'showcase.achievements.manage',
                'showcase.submit_review',
            ],
            CoreRole::Editor->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'downloads.protected.access',
                'requests.commentPublic',
                'requests.files.download',
                'cms.pages.view',
                'cms.pages.update',
                'cms.pages.compose',
                'cms.pages.use_reusable_blocks',
                'cms.pages.preview',
                'cms.pages.submit_review',
                'cms.blocks.use_approved_only',
                'products.view',
                'blog.view',
                'blog.create',
                'blog.update',
                'blog.preview',
                'blog.submit_review',
                'faq.view',
                'faq.create',
                'faq.update',
                'faq.submit_review',
                'careers.view',
                'careers.create',
                'careers.update',
                'careers.submit_review',
                'showcase.testimonials.manage',
                'showcase.partners.manage',
                'showcase.team.manage',
                'showcase.timeline.manage',
                'showcase.achievements.manage',
                'showcase.submit_review',
            ],
            CoreRole::ProductManager->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'downloads.protected.access',
                'requests.viewAny',
                'requests.commentInternal',
                'requests.files.download',
                'cms.pages.view',
                'cms.pages.update',
                'cms.pages.compose',
                'cms.pages.use_reusable_blocks',
                'cms.pages.preview',
                'cms.pages.submit_review',
                'cms.blocks.use_approved_only',
                'products.view',
                'products.create',
                'products.update',
                'products.preview',
                'products.submit_review',
                'products.categories.manage',
                'products.tags.manage',
                'products.reviews.moderate',
            ],
            CoreRole::SalesManager->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'downloads.protected.access',
                'requests.viewAny',
                'requests.updateStatus',
                'requests.commentInternal',
                'requests.commentPublic',
                'requests.files.download',
                'cms.pages.view',
                'cms.pages.preview',
                'cms.approvals.view_queue',
                'products.view',
            ],
            CoreRole::Developer->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'downloads.protected.access',
                'cms.pages.view',
                'cms.pages.update',
                'cms.pages.compose',
                'cms.pages.use_reusable_blocks',
                'cms.pages.use_advanced_blocks',
                'cms.pages.preview',
                'cms.blocks.view_definitions',
                'products.view',
                'products.preview',
            ],
            CoreRole::SupportOperations->value => [
                'admin.access',
                'profile.view',
                'profile.update',
                'security.sessions.viewOwn',
                'security.devices.viewOwn',
                'security.events.view',
                'downloads.protected.access',
                'requests.viewAny',
                'requests.updateStatus',
                'requests.commentInternal',
                'requests.files.download',
                'cms.pages.view',
                'cms.pages.preview',
                'cms.approvals.view_queue',
                'products.view',
                'products.reviews.moderate',
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
                'requests.files.download',
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
