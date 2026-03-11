<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\AuditSecurity\Models\UserDevice;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use App\Modules\IdentityAccess\Models\Role;
use Carbon\CarbonImmutable;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SessionDeviceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_session_and_device_history_pages(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create();
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $user->assignRole($userRole);

        $this->actingAs($user)->get('/account/sessions')->assertOk();
        $this->actingAs($user)->get('/account/devices')->assertOk();
    }

    public function test_user_cannot_view_another_users_security_history_records(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $userA->assignRole($userRole);
        $userB->assignRole($userRole);

        $device = UserDevice::query()->create([
            'user_id' => $userB->getKey(),
            'device_fingerprint' => hash('sha256', 'device-b'),
            'first_seen_at' => CarbonImmutable::now(),
            'last_seen_at' => CarbonImmutable::now(),
        ]);

        $history = UserSessionHistory::query()->create([
            'user_id' => $userB->getKey(),
            'session_identifier' => 'session-b',
            'device_id' => $device->getKey(),
            'logged_in_at' => CarbonImmutable::now(),
            'last_activity_at' => CarbonImmutable::now(),
            'is_current' => true,
        ]);

        $this->assertFalse(Gate::forUser($userA)->allows('view', $history));
        $this->assertFalse(Gate::forUser($userA)->allows('view', $device));
    }
}
