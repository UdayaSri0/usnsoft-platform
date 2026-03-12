<?php

namespace App\Modules\Faq\Services;

use App\Modules\Faq\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FaqCatalogService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function publicListing(array $filters = []): LengthAwarePaginator
    {
        $query = Faq::query()
            ->publiclyVisible()
            ->with(['category', 'linkedProduct'])
            ->search(is_string($filters['q'] ?? null) ? $filters['q'] : null);

        if (is_string($filters['category'] ?? null) && trim($filters['category']) !== '') {
            $category = trim((string) $filters['category']);
            $query->whereHas('category', static fn (Builder $categoryQuery) => $categoryQuery->where('slug', $category));
        }

        if (is_string($filters['product'] ?? null) && trim($filters['product']) !== '') {
            $product = trim((string) $filters['product']);
            $query->whereHas('linkedProduct', static fn (Builder $productQuery) => $productQuery->where('slug_current', $product));
        }

        return $query
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->paginate(24)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Faq>
     */
    public function forBlock(
        int $limit = 6,
        ?string $categorySlug = null,
        ?string $productSlug = null,
        bool $featuredOnly = false,
    ): Collection
    {
        $query = Faq::query()
            ->publiclyVisible()
            ->with('category')
            ->when($featuredOnly, fn (Builder $builder) => $builder->where('featured_flag', true))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order');

        if ($categorySlug !== null && $categorySlug !== '') {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug));
        }

        if ($productSlug !== null && $productSlug !== '') {
            $query->whereHas('linkedProduct', fn (Builder $productQuery) => $productQuery->where('slug_current', $productSlug));
        }

        return $query->limit($limit)->get();
    }
}
