<?php

namespace App\Modules\Products\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Requests\ProductReviewStoreRequest;
use App\Modules\Products\Services\ProductCatalogService;
use App\Modules\Products\Services\ProductReviewService;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
        private readonly ProductReviewService $reviewService,
    ) {}

    public function store(ProductReviewStoreRequest $request, Product $product): RedirectResponse
    {
        $resolved = $this->catalogService->resolvePublicProduct($product->slug_current);

        abort_unless($resolved, 404);

        $this->reviewService->submit($resolved, $request->user(), $request->validated());

        return redirect()
            ->to(route('products.show', ['product' => $resolved->slug_current]).'#reviews')
            ->with('status', 'product-review-submitted');
    }
}
