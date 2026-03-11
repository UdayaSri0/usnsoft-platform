<?php

namespace App\Modules\Products\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Products\Enums\ProductPermission;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductDownload;
use App\Modules\Products\Services\ProductDownloadService;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(ProductPermission::View->value);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(ProductPermission::Create->value);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission(ProductPermission::Update->value);
    }

    public function submitForReview(User $user, Product $product): bool
    {
        return $this->update($user, $product)
            && $user->hasPermission(ProductPermission::SubmitReview->value);
    }

    public function preview(User $user, Product $product): bool
    {
        return $this->view($user, $product)
            && $user->hasPermission(ProductPermission::Preview->value);
    }

    public function approve(User $user, Product $product): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Approve->value);
    }

    public function reject(User $user, Product $product): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Reject->value);
    }

    public function publish(User $user, Product $product): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Publish->value);
    }

    public function schedule(User $user, Product $product): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Schedule->value);
    }

    public function archive(User $user, Product $product): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(ProductPermission::Archive->value);
    }

    public function download(User $user, Product $product, ProductDownload $download): bool
    {
        return app(ProductDownloadService::class)->authorize($user, $product, $download);
    }

    public function submitReview(User $user, Product $product): bool
    {
        return $user->isActiveForAuthentication() && $user->hasVerifiedEmail();
    }
}
