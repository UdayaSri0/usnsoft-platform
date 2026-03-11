<?php

namespace Tests\Feature\Products;

use App\Enums\CoreRole;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Products\Services\ProductWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Products\Concerns\InteractsWithProductPlatform;
use Tests\TestCase;

class ProductVisibilityAccessTest extends TestCase
{
    use InteractsWithProductPlatform;
    use RefreshDatabase;

    public function test_guest_can_view_a_public_product_page(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $product = $this->createPublishedProduct($superAdmin);

        $response = $this->get(route('products.show', ['product' => $product->slug_current]));

        $response->assertOk();
        $response->assertSee($product->name_current);
    }

    public function test_unlisted_product_is_hidden_from_listing_but_accessible_by_direct_url(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $publicProduct = $this->createPublishedProduct($superAdmin, [
            'name' => 'Public Catalog Product',
            'slug' => 'public-catalog-product',
        ]);
        $unlistedProduct = $this->createPublishedProduct($superAdmin, [
            'name' => 'Unlisted Product',
            'slug' => 'unlisted-product',
            'product_visibility' => ProductVisibility::Unlisted->value,
        ]);

        $listing = $this->get(route('products.index'));
        $direct = $this->get(route('products.show', ['product' => $unlistedProduct->slug_current]));

        $listing->assertOk();
        $listing->assertSee($publicProduct->name_current);
        $listing->assertDontSee($unlistedProduct->name_current);

        $direct->assertOk();
        $direct->assertSee($unlistedProduct->name_current);
    }

    public function test_private_product_cannot_be_publicly_viewed(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $product = $this->createPublishedProduct($superAdmin, [
            'product_visibility' => ProductVisibility::Private->value,
            'slug' => 'private-product',
        ]);

        $this->get(route('products.show', ['product' => $product->slug_current]))->assertNotFound();
    }

    public function test_scheduled_product_is_not_public_before_publish_at(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $publishAt = CarbonImmutable::now()->addMinutes(15);
        $product = $this->createScheduledProduct($superAdmin, $superAdmin, $publishAt, [
            'slug' => 'scheduled-product',
            'name' => 'Scheduled Product',
        ]);

        $this->get(route('products.show', ['product' => $product->slug_current]))->assertNotFound();
    }

    public function test_scheduled_product_becomes_public_after_publish_conditions_are_met(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $publishAt = CarbonImmutable::now()->addMinutes(10);
        $product = $this->createScheduledProduct($superAdmin, $superAdmin, $publishAt, [
            'slug' => 'scheduled-public-product',
            'name' => 'Scheduled Public Product',
        ]);

        app(ProductWorkflowService::class)->processScheduledTransitions($publishAt->addMinute());
        $product->refresh();

        $response = $this->get(route('products.show', ['product' => $product->slug_current]));

        $response->assertOk();
        $response->assertSee('Scheduled Public Product');
    }
}
