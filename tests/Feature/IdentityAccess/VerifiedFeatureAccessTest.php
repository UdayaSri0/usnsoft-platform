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

    public function test_verified_email_is_required_for_client_request_and_verified_download_routes_but_not_authenticated_downloads(): void
    {
        $this->seedProductPlatformCore();

        $userRole = Role::query()->where('name', CoreRole::User->value)->firstOrFail();
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $verifiedDownloadProduct = $this->createPublishedProduct($superAdmin, [
            'slug' => 'verified-feature-download-product',
        ]);
        $verifiedDownload = $verifiedDownloadProduct->currentPublishedVersion->downloads->firstOrFail();
        $authenticatedDownloadProduct = $this->createPublishedProduct($superAdmin, [
            'slug' => 'authenticated-download-product',
            'download_visibility' => \App\Modules\Products\Enums\ProductDownloadVisibility::Authenticated->value,
            'downloads' => [[
                'label' => 'Authenticated release',
                'description' => 'Authenticated users can access this without email verification.',
                'version_label' => 'v1.0.0',
                'download_mode' => \App\Modules\Products\Enums\ProductDownloadMode::ProtectedPrivateDownload->value,
                'visibility' => \App\Modules\Products\Enums\ProductDownloadVisibility::Authenticated->value,
                'media_asset_id' => $this->createMediaAsset($superAdmin, 'local', 'products/releases/authenticated-download-test.zip', 'authenticated-download-test.zip')->getKey(),
                'is_primary' => true,
                'review_eligible' => true,
            ]],
        ]);
        $authenticatedDownload = $authenticatedDownloadProduct->currentPublishedVersion->downloads->firstOrFail();

        $unverifiedUser = User::factory()->unverified()->create();
        $unverifiedUser->assignRole($userRole);

        $this->actingAs($unverifiedUser)
            ->get('/client-requests/new')
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $this->actingAs($unverifiedUser)
            ->get(route('products.downloads.show', ['product' => $verifiedDownloadProduct->slug_current, 'download' => $verifiedDownload->getKey()]))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'verification-required-for-protected-features');

        $this->actingAs($unverifiedUser)
            ->get(route('products.downloads.show', ['product' => $authenticatedDownloadProduct->slug_current, 'download' => $authenticatedDownload->getKey()]))
            ->assertOk()
            ->assertDownload('authenticated-download-test.zip');

        $verifiedUser = User::factory()->create();
        $verifiedUser->assignRole($userRole);

        $this->actingAs($verifiedUser)
            ->get('/client-requests/new')
            ->assertOk();

        $this->actingAs($verifiedUser)
            ->get(route('products.downloads.show', ['product' => $verifiedDownloadProduct->slug_current, 'download' => $verifiedDownload->getKey()]))
            ->assertOk();
    }
}
