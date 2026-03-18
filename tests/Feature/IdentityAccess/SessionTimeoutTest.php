<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Carbon\CarbonImmutable;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_session_timeout_logs_the_user_out_when_idle_window_is_exceeded(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        config([
            'security.session.internal_idle_timeout_minutes' => 5,
            'security.enforce_internal_mfa' => false,
        ]);

        $admin = User::factory()->create();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->withSession([
                'security.session.last_activity_at' => CarbonImmutable::now()->subMinutes(10)->toIso8601String(),
            ])
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('security_events', [
            'event_type' => 'session.timeout',
            'user_id' => $admin->getKey(),
        ]);
    }
}
