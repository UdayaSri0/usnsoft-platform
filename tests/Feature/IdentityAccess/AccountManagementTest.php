<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Products\Concerns\InteractsWithProductPlatform;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use InteractsWithProductPlatform;
    use RefreshDatabase;

    public function test_super_admin_can_create_user_admin_and_super_admin_accounts_through_account_management(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.accounts.store'), $this->payloadFor('managed-user@example.com', $userRole))
            ->assertRedirect();

        $this->actingAs($superAdmin)
            ->post(route('admin.accounts.store'), $this->payloadFor('managed-admin@example.com', $adminRole))
            ->assertRedirect();

        $this->actingAs($superAdmin)
            ->post(route('admin.accounts.store'), $this->payloadFor('managed-super-admin@example.com', $superAdminRole))
            ->assertRedirect();

        $this->assertTrue(User::query()->where('email', 'managed-user@example.com')->firstOrFail()->hasRole(CoreRole::User));
        $this->assertTrue(User::query()->where('email', 'managed-admin@example.com')->firstOrFail()->hasRole(CoreRole::Admin));
        $this->assertTrue(User::query()->where('email', 'managed-super-admin@example.com')->firstOrFail()->hasRole(CoreRole::SuperAdmin));
    }

    public function test_admin_can_create_user_but_cannot_create_admin_or_super_admin_accounts(): void
    {
        $this->seedProductPlatformCore();

        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.accounts.store'), $this->payloadFor('standard-user@example.com', $userRole))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.accounts.store'), $this->payloadFor('blocked-admin@example.com', $adminRole))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.accounts.store'), $this->payloadFor('blocked-super-admin@example.com', $superAdminRole))
            ->assertForbidden();

        $this->assertTrue(User::query()->where('email', 'standard-user@example.com')->firstOrFail()->hasRole(CoreRole::User));
        $this->assertDatabaseMissing('users', ['email' => 'blocked-admin@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'blocked-super-admin@example.com']);
    }

    public function test_admin_create_screen_hides_privileged_role_options(): void
    {
        $this->seedProductPlatformCore();

        $admin = $this->makeUserWithRole(CoreRole::Admin);

        $this->actingAs($admin)
            ->get(route('admin.accounts.create'))
            ->assertOk()
            ->assertSee('User')
            ->assertDontSee('Super Admin');
    }

    public function test_super_admin_can_change_role_and_role_change_is_audited(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $target = $this->makeUserWithRole(CoreRole::User);
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('admin.accounts.update', ['user' => $target->getKey()]), [
                'name' => $target->name,
                'email' => $target->email,
                'phone' => '555-0148',
                'role_id' => $adminRole->getKey(),
            ])
            ->assertRedirect();

        $this->assertTrue($target->fresh()->hasRole(CoreRole::Admin));
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'role.changed',
            'action' => 'assign_role',
            'auditable_type' => $target->getMorphClass(),
            'auditable_id' => $target->getKey(),
        ]);
    }

    public function test_admin_cannot_manage_internal_staff_account(): void
    {
        $this->seedProductPlatformCore();

        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $editor = $this->makeUserWithRole(CoreRole::Editor);

        $this->actingAs($admin)
            ->get(route('admin.accounts.edit', ['user' => $editor->getKey()]))
            ->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(string $email, Role $role): array
    {
        return [
            'name' => 'Managed Account',
            'email' => $email,
            'phone' => '555-0101',
            'role_id' => $role->getKey(),
            'password' => 'ChangeMe123!Secure',
            'password_confirmation' => 'ChangeMe123!Secure',
        ];
    }
}
