<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductCatalogService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function publicListing(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()
            ->publicCatalog()
            ->with([
                'currentPublishedVersion.category',
                'currentPublishedVersion.tags',
                'currentPublishedVersion.platforms',
            ])
            ->search(is_string($filters['q'] ?? null) ? $filters['q'] : null);

        if (is_string($filters['category'] ?? null) && trim($filters['category']) !== '') {
            $category = trim((string) $filters['category']);

            $query->whereHas('currentPublishedVersion.category', static function (Builder $categoryQuery) use ($category): void {
                $categoryQuery->where('slug', $category);
            });
        }

        if (is_string($filters['tag'] ?? null) && trim($filters['tag']) !== '') {
            $tag = trim((string) $filters['tag']);

            $query->whereHas('currentPublishedVersion.tags', static function (Builder $tagQuery) use ($tag): void {
                $tagQuery->where('slug', $tag);
            });
        }

        if (is_string($filters['platform'] ?? null) && trim($filters['platform']) !== '') {
            $platform = trim((string) $filters['platform']);

            $query->whereHas('currentPublishedVersion.platforms', static function (Builder $platformQuery) use ($platform): void {
                $platformQuery->where('platform', $platform);
            });
        }

        if (filter_var($filters['featured'] ?? false, FILTER_VALIDATE_BOOL)) {
            $query->where('featured_flag', true);
        }

        return $query
            ->orderByDesc('featured_flag')
            ->orderBy('name_current')
            ->paginate(12)
            ->withQueryString();
    }

    public function resolvePublicProduct(string $slug): ?Product
    {
        return Product::query()
            ->publiclyResolvable()
            ->with([
                'featuredImage',
                'approvedReviews.user',
                'currentPublishedVersion.category',
                'currentPublishedVersion.tags',
                'currentPublishedVersion.platforms',
                'currentPublishedVersion.relatedProducts.currentPublishedVersion',
                'currentPublishedVersion.faqItems',
                'currentPublishedVersion.screenshots.mediaAsset',
                'currentPublishedVersion.downloads.mediaAsset',
                'currentPublishedVersion.seoMeta.ogImage',
                'currentPublishedVersion.featuredImage',
            ])
            ->where('slug_current', $slug)
            ->first();
    }
}
