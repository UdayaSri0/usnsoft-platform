<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Products\Concerns\InteractsWithProductPlatform;
use Tests\TestCase;

class VerifiedFeatureAccessTest extends TestCase
{
    use InteractsWithProductPlatform;
    use RefreshDatabase;

    public function test_verified_email_is_required_for_client_request_and_download_routes(): void
    {
        $this->seedProductPlatformCore();

        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'verified-feature-download-product',
        ]);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $unverifiedUser = User::factory()->unverified()->create();
        $unverifiedUser->assignRole($userRole);

        $this->actingAs($unverifiedUser)
            ->get('/client-requests/new')
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $this->actingAs($unverifiedUser)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $verifiedUser = User::factory()->create();
        $verifiedUser->assignRole($userRole);

        $this->actingAs($verifiedUser)
            ->get('/client-requests/new')
            ->assertOk();

        $this->actingAs($verifiedUser)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]))
            ->assertOk();
    }
}
