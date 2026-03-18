<?php

namespace Tests\Feature\Admin;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityCenterAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_user_cannot_access_security_center(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create();
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $user->assignRole($userRole);

        $this->actingAs($user)
            ->get(route('admin.security.index'))
            ->assertForbidden();
    }

    public function test_support_operations_can_access_security_center(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        config(['security.enforce_internal_mfa' => false]);

        $user = User::factory()->create();
        $role = Role::query()->where('name', CoreRole::SupportOperations->value)->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.security.index'))
            ->assertOk()
            ->assertSee('Security Center');
    }
}
