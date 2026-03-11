<?php

namespace Tests\Feature\Products;

use App\Enums\CoreRole;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Products\Models\ProductDownloadAccess;
use App\Modules\Products\Models\ProductUserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Products\Concerns\InteractsWithProductPlatform;
use Tests\TestCase;

class ProductDownloadFlowTest extends TestCase
{
    use InteractsWithProductPlatform;
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_download(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $product = $this->createPublishedProduct($superAdmin);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $this->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]))
            ->assertRedirect(route('login'));
    }

    public function test_logged_in_unauthorized_user_cannot_access_private_download(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'product_visibility' => ProductVisibility::Private->value,
            'download_visibility' => ProductDownloadVisibility::Internal->value,
            'downloads' => [[
                'label' => 'Internal package',
                'description' => 'Internal only download.',
                'version_label' => 'v1.0.0',
                'download_mode' => ProductDownloadMode::ProtectedPrivateDownload->value,
                'visibility' => ProductDownloadVisibility::Internal->value,
                'media_asset_id' => $this->createMediaAsset($superAdmin, 'local', 'products/releases/internal-test.zip', 'internal-test.zip')->getKey(),
                'is_primary' => true,
                'review_eligible' => true,
            ]],
        ]);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $this->actingAs($user)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]))
            ->assertForbidden();
    }

    public function test_logged_in_eligible_user_can_download_authorized_product_and_gain_review_verification(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'protected-download-product',
        ]);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]));

        $response->assertOk();
        $response->assertDownload('test-release.zip');

        $this->assertDatabaseHas('product_download_accesses', [
            'product_id' => $product->getKey(),
            'product_download_id' => $download->getKey(),
            'user_id' => $user->getKey(),
            'access_granted' => true,
        ]);

        $this->assertTrue(ProductUserVerification::query()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->exists());
    }

    public function test_external_download_mode_redirects_after_authorization_and_logging(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'external-download-product',
            'download_visibility' => ProductDownloadVisibility::Authenticated->value,
            'downloads' => [[
                'label' => 'External docs package',
                'description' => 'Redirect to trusted docs.',
                'version_label' => 'v1.0.0',
                'download_mode' => ProductDownloadMode::ExternalLink->value,
                'visibility' => ProductDownloadVisibility::Authenticated->value,
                'external_url' => 'https://downloads.example.test/release',
                'is_primary' => true,
                'review_eligible' => true,
            ]],
        ]);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]));

        $response->assertRedirect('https://downloads.example.test/release');
        $this->assertDatabaseHas('product_download_accesses', [
            'product_id' => $product->getKey(),
            'product_download_id' => $download->getKey(),
            'user_id' => $user->getKey(),
            'access_granted' => true,
        ]);
    }

    public function test_protected_asset_route_does_not_reveal_private_storage_path(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'secure-download-product',
        ]);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]));

        $response->assertOk();
        $response->assertDownload('test-release.zip');
        $this->assertNull($response->headers->get('Location'));
        $this->assertStringNotContainsString(
            'products/releases/',
            json_encode($response->headers->all(), JSON_THROW_ON_ERROR)
        );

        $access = ProductDownloadAccess::query()->latest('id')->firstOrFail();
        $this->assertTrue($access->access_granted);
    }
}
