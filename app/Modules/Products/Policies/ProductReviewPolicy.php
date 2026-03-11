<?php

namespace App\Modules\Products\Policies;

use App\Models\User;
use App\Modules\Products\Enums\ProductPermission;
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
}
