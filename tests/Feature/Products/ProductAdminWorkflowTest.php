<?php

namespace Tests\Feature\Products;

use App\Enums\CoreRole;
use App\Enums\ContentWorkflowState;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Services\ProductWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Products\Concerns\InteractsWithProductPlatform;
use Tests\TestCase;

class ProductAdminWorkflowTest extends TestCase
{
    use InteractsWithProductPlatform;
    use RefreshDatabase;

    public function test_review_moderation_permissions_are_enforced(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $reviewer = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'permission-review-product',
        ]);
        $verification = $this->createReviewVerification($superAdmin, $reviewer, $product);
        $review = $this->createReview($product, $reviewer, ProductReviewState::Pending, 5, $verification);

        $this->actingAs($user)
            ->put(route('admin.products.reviews.moderate', ['review' => $review->getKey()]), [
                'state' => ProductReviewState::Approved->value,
            ])
            ->assertForbidden();
    }

    public function test_non_super_admin_cannot_publish_product_version(): void
    {
        $this->seedProductPlatformCore();

        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $product = $this->createProductWithDraft($admin, [
            'slug' => 'publish-guard-product',
        ]);
        $draft = $product->currentDraftVersion()->firstOrFail();
        $workflow = app(ProductWorkflowService::class);

        $workflow->submitForReview($draft, $admin);
        $workflow->approve($draft, $superAdmin);
        $workflow->confirmPreview($draft, $superAdmin);

        $response = $this->actingAs($admin)->post(route('admin.products.versions.publish', ['version' => $draft->getKey()]), [
            'preview_confirmed' => 1,
        ]);

        $response->assertForbidden();
        $draft->refresh();
        $this->assertSame(ContentWorkflowState::Approved, $draft->workflow_state);
    }

    public function test_super_admin_can_publish_an_approved_previewed_product_version(): void
    {
        $this->seedProductPlatformCore();

        $productManager = $this->makeUserWithRole(CoreRole::ProductManager);
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $product = $this->createProductWithDraft($productManager, [
            'slug' => 'publish-success-product',
        ]);
        $draft = $product->currentDraftVersion()->firstOrFail();
        $workflow = app(ProductWorkflowService::class);

        $workflow->submitForReview($draft, $productManager);
        $workflow->approve($draft, $superAdmin);

        $response = $this->actingAs($superAdmin)->post(route('admin.products.versions.publish', ['version' => $draft->getKey()]), [
            'preview_confirmed' => 1,
        ]);

        $response->assertRedirect();
        $product->refresh();
        $draft->refresh();

        $this->assertSame(ContentWorkflowState::Published, $draft->workflow_state);
        $this->assertSame($draft->getKey(), $product->current_published_version_id);
    }

    public function test_category_and_tag_management_actions_are_audited(): void
    {
        $this->seedProductPlatformCore();

        $productManager = $this->makeUserWithRole(CoreRole::ProductManager);

        $this->actingAs($productManager)
            ->post(route('admin.products.categories.store'), [
                'name' => 'Utilities',
                'slug' => 'utilities',
                'description' => 'Utility products.',
                'sort_order' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->actingAs($productManager)
            ->post(route('admin.products.tags.store'), [
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security related products.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'products.category.created',
            'action' => 'create_product_category',
            'actor_id' => $productManager->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'products.tag.created',
            'action' => 'create_product_tag',
            'actor_id' => $productManager->getKey(),
        ]);
    }
}
