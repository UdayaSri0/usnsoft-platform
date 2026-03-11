<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AccountDeletionRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_is_stored_without_hard_deleting_user(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create();
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $user->assignRole($userRole);

        $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
            'reason' => 'Need to close this account',
        ])->assertRedirect('/profile');

        $request = AccountDeletionRequest::query()->where('user_id', $user->getKey())->firstOrFail();

        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $request->getKey(),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::AccountDeletionRequested->value,
            'user_id' => $user->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => SecurityEventType::AccountDeletionRequested->value,
            'actor_id' => $user->getKey(),
        ]);
    }

    public function test_review_access_is_restricted_for_non_privileged_users(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $admin = User::factory()->create();

        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();

        $owner->assignRole($userRole);
        $viewer->assignRole($userRole);
        $admin->assignRole($adminRole);

        $request = AccountDeletionRequest::query()->create([
            'user_id' => $owner->getKey(),
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->assertFalse(Gate::forUser($viewer)->allows('review', $request));
        $this->assertTrue(Gate::forUser($admin)->allows('review', $request));
    }
}
