<?php

namespace App\Modules\Products\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductReview;
use App\Modules\Products\Requests\ProductReviewModerationRequest;
use App\Modules\Products\Services\ProductReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function __construct(
        private readonly ProductReviewService $reviewService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProductReview::class);

        $state = $request->string('state')->toString();
        $product = $request->string('product')->toString();
        $q = $request->string('q')->toString();

        $reviews = ProductReview::query()
            ->with(['product', 'user', 'moderator'])
            ->when($state !== '', fn ($query) => $query->where('moderation_state', $state))
            ->when($product !== '', fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('slug_current', $product)))
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($searchQuery) use ($q): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$q.'%')
                        ->orWhere('body', 'like', '%'.$q.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%'.$q.'%')->orWhere('name', 'like', '%'.$q.'%'))
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name_current', 'like', '%'.$q.'%'));
                });
            })
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.reviews.index', [
            'reviews' => $reviews,
            'products' => Product::query()->orderBy('name_current')->get(),
            'filters' => compact('state', 'product', 'q'),
            'states' => ProductReviewState::cases(),
        ]);
    }

    public function moderate(ProductReviewModerationRequest $request, ProductReview $review): RedirectResponse
    {
        $this->authorize('moderate', $review);

        $this->reviewService->moderate(
            review: $review,
            state: ProductReviewState::from($request->string('state')->toString()),
            actor: $request->user(),
            notes: $request->string('notes')->toString() ?: null,
        );

        return back()->with('status', 'product-review-moderated');
    }
}
