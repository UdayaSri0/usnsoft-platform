<?php

namespace App\Modules\Products\Services;

use App\Models\User;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductReview;
use App\Modules\Products\Models\ProductUserVerification;

class ProductReviewEligibilityService
{
    /**
     * @return array{allowed: bool, reason: string|null, verification: ProductUserVerification|null}
     */
    public function evaluate(?User $user, Product $product): array
    {
        $version = $product->currentPublishedVersion;

        if (! $user) {
            return ['allowed' => false, 'reason' => 'login_required', 'verification' => null];
        }

        if (! $user->isActiveForAuthentication()) {
            return ['allowed' => false, 'reason' => 'inactive_account', 'verification' => null];
        }

        if (! $user->hasVerifiedEmail()) {
            return ['allowed' => false, 'reason' => 'verified_email_required', 'verification' => null];
        }

        if (! $version || ! $version->reviews_enabled) {
            return ['allowed' => false, 'reason' => 'reviews_disabled', 'verification' => null];
        }

        $existingReview = ProductReview::query()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->whereNull('deleted_at')
            ->exists();

        if ($existingReview) {
            return ['allowed' => false, 'reason' => 'existing_review', 'verification' => null];
        }

        $verification = ProductUserVerification::query()
            ->active()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->latest('verified_at')
            ->first();

        if (! $verification && $version->review_requires_verification) {
            return ['allowed' => false, 'reason' => 'verification_required', 'verification' => null];
        }

        return ['allowed' => true, 'reason' => null, 'verification' => $verification];
    }

    public function reasonMessage(?string $reason): string
    {
        return match ($reason) {
            'login_required' => 'Log in to submit a review for this product.',
            'inactive_account' => 'This account is not active for review submission.',
            'verified_email_required' => 'Verify your email address before submitting a review.',
            'reviews_disabled' => 'Reviews are currently disabled for this product.',
            'existing_review' => 'You already have an active review for this product.',
            'verification_required' => 'A verified download or approved verification record is required before reviewing this product.',
            default => 'You are not eligible to review this product yet.',
        };
    }
}
