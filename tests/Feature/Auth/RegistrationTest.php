<?php

namespace Tests\Feature\Auth;

use App\Enums\CoreRole;
use App\Models\User;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(CoreRole::User));
        $this->assertSame(1, $user->roles()->count());
        $this->assertFalse($user->is_internal);
    }

    public function test_tampered_registration_payload_cannot_assign_internal_roles(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $this->post('/register', [
            'name' => 'Tampered User',
            'email' => 'tampered@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => CoreRole::Admin->value,
            'is_internal' => true,
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'tampered@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(CoreRole::User));
        $this->assertFalse($user->hasAnyRole([CoreRole::Admin, CoreRole::SuperAdmin]));
        $this->assertFalse($user->is_internal);
    }
}
