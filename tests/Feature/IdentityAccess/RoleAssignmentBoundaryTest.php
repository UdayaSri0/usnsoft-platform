<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Services\RoleAssignmentService;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAssignmentBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_assign_internal_role(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
        ]);

        $superAdmin = User::factory()->create();
        $targetUser = User::factory()->create();

        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $editorRole = Role::query()->where('name', CoreRole::Editor->value)->firstOrFail();

        $superAdmin->assignRole($superAdminRole, $superAdmin->getKey());

        app(RoleAssignmentService::class)->assign($superAdmin, $targetUser, $editorRole);

        $this->assertTrue($targetUser->fresh()->hasRole(CoreRole::Editor));
    }

    public function test_admin_cannot_assign_internal_role_even_with_assign_permission(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
        ]);

        $admin = User::factory()->create();
        $targetUser = User::factory()->create();

        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $editorRole = Role::query()->where('name', CoreRole::Editor->value)->firstOrFail();
        $assignPermission = Permission::query()->where('name', 'identity.roles.assign')->firstOrFail();

        $admin->assignRole($adminRole, null);
        $adminRole->permissions()->syncWithoutDetaching([$assignPermission->getKey()]);

        $this->expectException(AuthorizationException::class);

        app(RoleAssignmentService::class)->assign($admin, $targetUser, $editorRole);
    }
}
