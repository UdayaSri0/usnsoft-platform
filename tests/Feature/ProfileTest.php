<?php

namespace Tests\Feature;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Enums\AccountDeletionRequestStatus;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createStandardUser(): User
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $user = User::factory()->create();
        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $user->assignRole($userRole);

        return $user;
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->createStandardUser();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createStandardUser();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+94111222333',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('+94111222333', $user->phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->createStandardUser();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_request_account_deletion(): void
    {
        $user = $this->createStandardUser();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
                'reason' => 'No longer needed',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->getKey(),
            'status' => AccountDeletionRequestStatus::Pending->value,
        ]);
    }

    public function test_correct_password_must_be_provided_to_request_account_deletion(): void
    {
        $user = $this->createStandardUser();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
