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

class VerifiedFeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_email_is_required_for_client_request_and_download_routes(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();

        $unverifiedUser = User::factory()->unverified()->create();
        $unverifiedUser->assignRole($userRole);

        $this->actingAs($unverifiedUser)
            ->get('/client-requests/new')
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $this->actingAs($unverifiedUser)
            ->get('/products/1/download')
            ->assertRedirect(route('verification.notice'));

        $verifiedUser = User::factory()->create();
        $verifiedUser->assignRole($userRole);

        $this->actingAs($verifiedUser)
            ->get('/client-requests/new')
            ->assertOk();

        $this->actingAs($verifiedUser)
            ->get('/products/1/download')
            ->assertOk();
    }
}
