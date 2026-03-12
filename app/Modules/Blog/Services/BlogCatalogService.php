<?php

namespace App\Modules\Blog\Services;

use App\Modules\Blog\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BlogCatalogService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function publicListing(array $filters = []): LengthAwarePaginator
    {
        $query = BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'tags', 'author', 'featuredImage'])
            ->search(is_string($filters['q'] ?? null) ? $filters['q'] : null);

        if (is_string($filters['category'] ?? null) && trim($filters['category']) !== '') {
            $category = trim((string) $filters['category']);

            $query->whereHas('category', static function (Builder $categoryQuery) use ($category): void {
                $categoryQuery->where('slug', $category);
            });
        }

        if (is_string($filters['tag'] ?? null) && trim($filters['tag']) !== '') {
            $tag = trim((string) $filters['tag']);

            $query->whereHas('tags', static function (Builder $tagQuery) use ($tag): void {
                $tagQuery->where('slug', $tag);
            });
        }

        if (filter_var($filters['featured'] ?? false, FILTER_VALIDATE_BOOL)) {
            $query->where('featured_flag', true);
        }

        return $query
            ->orderByDesc('featured_flag')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();
    }

    public function resolvePublicPost(string $slug): ?BlogPost
    {
        return BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'tags', 'author', 'featuredImage', 'seoMeta.ogImage', 'relatedPosts.category', 'relatedPosts.author'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function latestPublished(int $limit = 3, ?string $categorySlug = null, ?string $tagSlug = null): Collection
    {
        $query = BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'author', 'featuredImage'])
            ->orderByDesc('featured_flag')
            ->orderByDesc('published_at');

        if ($categorySlug !== null && $categorySlug !== '') {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug));
        }

        if ($tagSlug !== null && $tagSlug !== '') {
            $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('slug', $tagSlug));
        }

        return $query->limit($limit)->get();
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function featuredPublished(int $limit = 3): Collection
    {
        return BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'author', 'featuredImage'])
            ->where('featured_flag', true)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  list<string>  $slugs
     * @return Collection<int, BlogPost>
     */
    public function manualSelection(array $slugs, int $limit = 3): Collection
    {
        $orderedSlugs = collect($slugs)
            ->filter(fn (mixed $slug): bool => is_string($slug) && trim($slug) !== '')
            ->map(fn (string $slug): string => trim($slug))
            ->unique()
            ->take($limit)
            ->values();

        if ($orderedSlugs->isEmpty()) {
            return collect();
        }

        $posts = BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'author', 'featuredImage'])
            ->whereIn('slug', $orderedSlugs->all())
            ->get()
            ->keyBy('slug');

        return $orderedSlugs
            ->map(fn (string $slug): ?BlogPost => $posts->get($slug))
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function related(BlogPost $post, int $limit = 3): Collection
    {
        $explicit = $post->relatedPosts()
            ->publiclyVisible()
            ->with(['category', 'author', 'featuredImage'])
            ->limit($limit)
            ->get();

        if ($explicit->count() >= $limit) {
            return $explicit;
        }

        $excludeIds = $explicit->pluck('id')->push($post->getKey())->all();

        $fallback = BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'author', 'featuredImage'])
            ->whereKeyNot($excludeIds)
            ->when($post->blog_category_id, fn (Builder $query) => $query->where('blog_category_id', $post->blog_category_id))
            ->orderByDesc('published_at')
            ->limit($limit - $explicit->count())
            ->get();

        return $explicit->concat($fallback)->values();
    }
}
