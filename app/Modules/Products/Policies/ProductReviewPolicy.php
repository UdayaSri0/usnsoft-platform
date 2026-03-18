<?php

namespace App\Modules\Products\Policies;

use App\Models\User;
use App\Modules\Products\Enums\ProductPermission;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Models\ProductReview;

class ProductReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ProductPermission::ReviewsModerate->value);
    }

    public function moderate(User $user, ProductReview $review): bool
    {
        return $this->viewAny($user);
    }

    public function moderateState(User $user, ProductReview $review, ProductReviewState $state): bool
    {
        if (! $this->moderate($user, $review)) {
            return false;
        }

        if ($state->requiresHiddenContentPermission() && ! $user->hasPermission('moderation.hidden.manage')) {
            return false;
        }

        return true;
    }

    public function viewInternalNotes(User $user, ProductReview $review): bool
    {
        return $this->moderate($user, $review)
            && $user->hasPermission('moderation.notes.view');
    }
}
