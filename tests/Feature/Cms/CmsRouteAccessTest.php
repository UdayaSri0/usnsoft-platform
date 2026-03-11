<?php

namespace Tests\Feature\Cms;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CmsBlockDefinitionSeeder;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_public_user_cannot_access_cms_admin_routes(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
            CmsBlockDefinitionSeeder::class,
        ]);

        $this->get(route('admin.cms.pages.index'))->assertRedirect('/login');

        $publicUser = User::factory()->create();
        $publicRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $publicUser->assignRole($publicRole);

        $this->actingAs($publicUser)
            ->get(route('admin.cms.pages.index'))
            ->assertForbidden();
    }

    public function test_internal_staff_with_permission_can_access_cms_pages_listing(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
            CmsBlockDefinitionSeeder::class,
        ]);

        $admin = User::factory()->create();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get(route('admin.cms.pages.index'))
            ->assertOk();
    }
}
