<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Services\MfaService;
use App\Modules\IdentityAccess\Services\TotpService;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class StaffMfaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_staff_is_redirected_to_mfa_setup_when_enforcement_is_enabled(): void
    {
        $this->seedSecurityCore();
        config(['security.enforce_internal_mfa' => true]);

        $admin = $this->makeUserWithRole(CoreRole::Admin);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('account.security.mfa.edit'));
    }

    public function test_internal_staff_can_enroll_in_mfa_and_then_access_dashboard(): void
    {
        $this->seedSecurityCore();
        config(['security.enforce_internal_mfa' => true]);

        $admin = $this->makeUserWithRole(CoreRole::Admin);

        $this->actingAs($admin)
            ->post(route('account.security.mfa.start'))
            ->assertRedirect(route('account.security.mfa.edit'));

        $enrollment = app(MfaService::class)->enrollmentData($admin);
        $this->assertNotNull($enrollment);

        $code = app(TotpService::class)->currentCode($enrollment['secret']);

        $this->actingAs($admin)
            ->post(route('account.security.mfa.confirm'), [
                'code' => $code,
            ])
            ->assertRedirect(route('account.security.mfa.edit'));

        $this->assertNotNull($admin->fresh()->mfa_enabled_at);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_internal_staff_with_enabled_mfa_must_complete_challenge_for_a_new_session(): void
    {
        $this->seedSecurityCore();
        config(['security.enforce_internal_mfa' => true]);

        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $this->enableMfaFor($admin);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('account.security.mfa.challenge'));

        $activeMethod = app(MfaService::class)->activeMethod($admin);
        $this->assertNotNull($activeMethod);

        $code = app(TotpService::class)->currentCode(
            Crypt::decryptString($activeMethod->secret_encrypted)
        );

        $this->actingAs($admin)
            ->post(route('account.security.mfa.challenge.verify'), [
                'code' => $code,
            ])
            ->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk();
    }

    private function seedSecurityCore(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }

    private function makeUserWithRole(CoreRole $role): User
    {
        $user = User::factory()->create();
        $roleModel = Role::query()->where('name', $role->value)->firstOrFail();
        $user->assignRole($roleModel);

        return $user->fresh();
    }

    private function enableMfaFor(User $user): void
    {
        $mfaService = app(MfaService::class);
        $totpService = app(TotpService::class);

        $mfaService->startEnrollment($user);
        $enrollment = $mfaService->enrollmentData($user);
        $this->assertNotNull($enrollment);

        $mfaService->confirmEnrollment($user, $totpService->currentCode($enrollment['secret']));
    }
}
