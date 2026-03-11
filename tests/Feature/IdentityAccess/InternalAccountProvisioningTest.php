<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalAccountProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_another_admin_or_super_admin_account(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $admin->assignRole($adminRole);

        $adminPayload = [
            'name' => 'Other Admin',
            'email' => 'other-admin@example.com',
            'password' => 'ChangeMe123!Secure',
            'password_confirmation' => 'ChangeMe123!Secure',
            'role_id' => $adminRole->getKey(),
        ];

        $superAdminPayload = [
            'name' => 'Other Super Admin',
            'email' => 'other-super-admin@example.com',
            'password' => 'ChangeMe123!Secure',
            'password_confirmation' => 'ChangeMe123!Secure',
            'role_id' => $superAdminRole->getKey(),
        ];

        $this->actingAs($admin)->post('/admin/internal-accounts', $adminPayload)->assertForbidden();
        $this->actingAs($admin)->post('/admin/internal-accounts', $superAdminPayload)->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'other-admin@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'other-super-admin@example.com']);
    }

    public function test_super_admin_can_create_internal_accounts(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $editorRole = Role::query()->where('name', CoreRole::Editor->value)->firstOrFail();
        $superAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($superAdmin)->post('/admin/internal-accounts', [
            'name' => 'Internal Editor',
            'email' => 'internal-editor@example.com',
            'password' => 'ChangeMe123!Secure',
            'password_confirmation' => 'ChangeMe123!Secure',
            'role_id' => $editorRole->getKey(),
        ]);

        $response->assertRedirect('/admin/internal-accounts/create');

        $createdUser = User::query()->where('email', 'internal-editor@example.com')->firstOrFail();
        $this->assertTrue($createdUser->hasRole(CoreRole::Editor));
        $this->assertTrue($createdUser->is_internal);
    }
}
