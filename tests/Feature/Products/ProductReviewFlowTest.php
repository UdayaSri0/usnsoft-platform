<?php

namespace Tests\Feature\Products;

use App\Enums\CoreRole;
use App\Modules\Products\Enums\ProductReviewState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Products\Concerns\InteractsWithProductPlatform;
use Tests\TestCase;

class ProductReviewFlowTest extends TestCase
{
    use InteractsWithProductPlatform;
    use RefreshDatabase;

    public function test_only_verified_downloader_can_submit_review(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'reviewable-product',
        ]);
        $download = $product->currentPublishedVersion->downloads->firstOrFail();

        $this->actingAs($user)
            ->get(route('products.downloads.show', ['product' => $product->slug_current, 'download' => $download->getKey()]))
            ->assertOk();

        $response = $this->actingAs($user)->post(route('products.reviews.store', ['product' => $product->slug_current]), [
            'rating' => 5,
            'title' => 'Verified review',
            'body' => 'This product was downloaded through the protected route and the review submission should be accepted.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'product-review-submitted');
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->getKey(),
            'user_id' => $user->getKey(),
            'moderation_state' => ProductReviewState::Pending->value,
        ]);
    }

    public function test_non_verified_user_cannot_submit_review(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'restricted-review-product',
        ]);

        $response = $this->from(route('products.show', ['product' => $product->slug_current]))
            ->actingAs($user)
            ->post(route('products.reviews.store', ['product' => $product->slug_current]), [
                'rating' => 4,
                'title' => 'Should fail',
                'body' => 'This should be blocked because the user has not downloaded the product or been verified.',
            ]);

        $response->assertRedirect(route('products.show', ['product' => $product->slug_current]));
        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_only_approved_reviews_appear_publicly(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $reviewerA = $this->makeUserWithRole(CoreRole::User);
        $reviewerB = $this->makeUserWithRole(CoreRole::User);
        $reviewerC = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'public-review-product',
        ]);

        $verificationA = $this->createReviewVerification($superAdmin, $reviewerA, $product);
        $verificationB = $this->createReviewVerification($superAdmin, $reviewerB, $product);
        $verificationC = $this->createReviewVerification($superAdmin, $reviewerC, $product);

        $approved = $this->createReview($product, $reviewerA, ProductReviewState::Approved, 5, $verificationA, $superAdmin);
        $pending = $this->createReview($product, $reviewerB, ProductReviewState::Pending, 4, $verificationB, $superAdmin);
        $hidden = $this->createReview($product, $reviewerC, ProductReviewState::Hidden, 3, $verificationC, $superAdmin);

        $response = $this->get(route('products.show', ['product' => $product->slug_current]));

        $response->assertOk();
        $response->assertSee($approved->body);
        $response->assertDontSee($pending->body);
        $response->assertDontSee($hidden->body);
    }

    public function test_internal_review_moderation_notes_are_hidden_from_public(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $reviewer = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'review-note-visibility-product',
        ]);
        $verification = $this->createReviewVerification($superAdmin, $reviewer, $product);

        $review = $this->createReview($product, $reviewer, ProductReviewState::Approved, 5, $verification, $superAdmin);
        $review->forceFill([
            'moderation_notes' => 'Internal review moderation note.',
        ])->save();

        $this->get(route('products.show', ['product' => $product->slug_current]))
            ->assertOk()
            ->assertSee($review->body)
            ->assertDontSee('Internal review moderation note.');
    }

    public function test_moderation_action_updates_public_aggregates(): void
    {
        $this->seedProductPlatformCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $reviewer = $this->makeUserWithRole(CoreRole::User);
        $product = $this->createPublishedProduct($superAdmin, [
            'slug' => 'moderation-product',
        ]);
        $verification = $this->createReviewVerification($superAdmin, $reviewer, $product);
        $review = $this->createReview($product, $reviewer, ProductReviewState::Pending, 4, $verification);

        $response = $this->actingAs($admin)->put(route('admin.products.reviews.moderate', ['review' => $review->getKey()]), [
            'state' => ProductReviewState::Approved->value,
            'notes' => 'Approved for publication.',
        ]);

        $response->assertRedirect();
        $review->refresh();
        $product->refresh();

        $this->assertSame(ProductReviewState::Approved, $review->moderation_state);
        $this->assertSame(1, $product->approved_review_count);
        $this->assertSame('4.00', number_format((float) $product->average_rating, 2, '.', ''));
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'products.review.moderated',
            'action' => 'moderate_product_review',
            'actor_id' => $admin->getKey(),
            'auditable_type' => 'product_review',
            'auditable_id' => $review->getKey(),
        ]);
    }
}
