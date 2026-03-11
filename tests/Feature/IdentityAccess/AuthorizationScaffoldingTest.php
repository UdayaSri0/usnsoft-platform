<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationScaffoldingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_gate_override_for_role_management(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();

        $superAdmin->assignRole($superAdminRole, null);

        $this->assertTrue(Gate::forUser($superAdmin)->allows('create', Role::class));
    }

    public function test_non_super_admin_cannot_assign_internal_role_even_with_assign_permission(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create();
        $targetUser = User::factory()->create();

        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $assignPermission = Permission::query()->where('name', 'users.assignRoles')->firstOrFail();

        $admin->assignRole($adminRole, null);
        $adminRole->permissions()->syncWithoutDetaching([$assignPermission->getKey()]);

        $this->assertFalse(Gate::forUser($admin)->allows('assignRole', [$targetUser, $superAdminRole]));
    }
}
