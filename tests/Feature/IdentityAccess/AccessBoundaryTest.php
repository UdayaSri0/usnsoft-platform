<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Enums\AccountStatus;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_internal_admin_area(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_standard_user_cannot_access_internal_admin_area(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create();
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_can_access_internal_admin_dashboard(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $this->actingAs($admin)->get('/admin/operations')->assertOk();
    }

    public function test_super_admin_can_access_internal_account_creation_screen(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $superAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($superAdmin)->get('/admin/internal-accounts/create');

        $response->assertOk();
    }

    public function test_non_admin_internal_role_cannot_access_admin_only_route(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $editor = User::factory()->create();
        $editorRole = Role::query()->where('name', CoreRole::Editor->value)->firstOrFail();
        $editor->assignRole($editorRole);

        $this->actingAs($editor)->get('/admin')->assertOk();
        $this->actingAs($editor)->get('/admin/operations')->assertForbidden();
    }

    public function test_deactivated_user_is_blocked_from_authenticated_routes(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create([
            'status' => AccountStatus::Deactivated,
            'deactivated_at' => now(),
        ]);
        $role = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect('/login');
    }

    public function test_unverified_internal_staff_is_redirected_to_email_verification_for_admin_panel(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $editor = User::factory()->unverified()->create([
            'is_internal' => true,
        ]);
        $role = Role::query()->where('name', CoreRole::Editor->value)->firstOrFail();
        $editor->assignRole($role);

        $this->actingAs($editor)->get('/admin')->assertRedirect(route('verification.notice'));
    }
}
