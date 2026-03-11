<?php

namespace App\Modules\Products\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductPlatform;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductCategory;
use App\Modules\Products\Models\ProductTag;
use App\Modules\Products\Models\ProductVersion;
use App\Modules\Products\Services\ProductCatalogService;
use App\Modules\Products\Services\ProductDownloadService;
use App\Modules\Products\Services\ProductReviewEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
        private readonly ProductDownloadService $downloadService,
        private readonly ProductReviewEligibilityService $reviewEligibilityService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->toString(),
            'category' => $request->string('category')->toString(),
            'tag' => $request->string('tag')->toString(),
            'platform' => $request->string('platform')->toString(),
            'featured' => $request->boolean('featured'),
        ];

        return view('products.index', [
            'products' => $this->catalogService->publicListing($filters),
            'filters' => $filters,
            'categories' => $this->categories(),
            'tags' => $this->tags(),
            'platforms' => ProductPlatform::cases(),
            'seo' => [
                'meta_title' => 'Products | '.config('app.name', 'USNsoft'),
                'meta_description' => 'Discover secure desktop, mobile, web, plugin, internal, and open-source products published through the USNsoft platform.',
                'canonical_url' => route('products.index'),
                'og_title' => 'Products | '.config('app.name', 'USNsoft'),
                'og_description' => 'Browse enterprise-ready product publishing, controlled downloads, and release visibility from USNsoft.',
            ],
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        $resolved = $this->catalogService->resolvePublicProduct($product->slug_current);

        abort_unless($resolved, 404);

        return $this->renderProductPage($request, $resolved, $resolved->currentPublishedVersion, false);
    }

    public function renderPreview(Request $request, ProductVersion $version, bool $isPreview = true): View
    {
        $product = $version->product()->with([
            'featuredImage',
            'approvedReviews.user',
        ])->firstOrFail();

        $version->loadMissing([
            'category',
            'tags',
            'platforms',
            'relatedProducts.currentPublishedVersion.category',
            'faqItems',
            'screenshots.mediaAsset',
            'downloads.mediaAsset',
            'seoMeta.ogImage',
            'featuredImage',
        ]);

        return $this->renderProductPage($request, $product, $version, $isPreview);
    }

    private function renderProductPage(Request $request, Product $product, ProductVersion $version, bool $isPreview): View
    {
        $approvedReviews = $product->approvedReviews->sortByDesc('published_at')->values();
        $reviewEligibility = $isPreview ? null : $this->reviewEligibilityService->evaluate($request->user(), $product);
        $downloads = $version->downloads;
        $primaryDownload = $downloads->sortByDesc('is_primary')->sortBy('sort_order')->first();

        return view('products.show', [
            'product' => $product,
            'version' => $version,
            'downloads' => $downloads,
            'primaryDownload' => $primaryDownload,
            'approvedReviews' => $approvedReviews,
            'reviewEligibility' => $reviewEligibility,
            'tabs' => $this->tabsFor($version, $downloads, $approvedReviews),
            'videoEmbedUrl' => $this->videoEmbedUrl($version->video_url),
            'relatedProducts' => $this->relatedProducts($version, $isPreview),
            'seo' => $this->seoPayload($product, $version, $isPreview),
            'isPreview' => $isPreview,
        ]);
    }

    /**
     * @param  Collection<int, \App\Modules\Products\Models\ProductDownload>  $downloads
     * @param  Collection<int, \App\Modules\Products\Models\ProductReview>  $approvedReviews
     * @return array<int, array{key: string, label: string}>
     */
    private function tabsFor(ProductVersion $version, Collection $downloads, Collection $approvedReviews): array
    {
        $tabs = [
            ['key' => 'overview', 'label' => 'Overview'],
        ];

        if ($version->screenshots->isNotEmpty()) {
            $tabs[] = ['key' => 'screenshots', 'label' => 'Screenshots'];
        }

        if ($version->video_url) {
            $tabs[] = ['key' => 'video', 'label' => 'Video'];
        }

        if (($version->changelog_visible && $version->changelog) || ($version->release_notes_visible && $version->release_notes)) {
            $tabs[] = ['key' => 'changelog', 'label' => 'Changelog'];
        }

        if ($version->documentation_link || $version->github_link) {
            $tabs[] = ['key' => 'documentation', 'label' => 'Documentation'];
        }

        if ($version->faqItems->isNotEmpty()) {
            $tabs[] = ['key' => 'faq', 'label' => 'FAQ'];
        }

        if ($version->reviews_enabled || $approvedReviews->isNotEmpty()) {
            $tabs[] = ['key' => 'reviews', 'label' => 'Reviews'];
        }

        if ($downloads->isNotEmpty()) {
            $tabs[] = ['key' => 'downloads', 'label' => 'Downloads'];
        }

        if ($version->support_contact || $downloads->contains(fn ($download) => $download->download_mode === ProductDownloadMode::ManualRequest)) {
            $tabs[] = ['key' => 'support', 'label' => 'Support'];
        }

        return $tabs;
    }

    /**
     * @return Collection<int, Product>
     */
    private function relatedProducts(ProductVersion $version, bool $isPreview): Collection
    {
        return $version->relatedProducts
            ->filter(function (Product $related) use ($isPreview): bool {
                if ($isPreview) {
                    return $related->currentPublishedVersion !== null;
                }

                return $related->visibility === ProductVisibility::Public
                    && $related->currentPublishedVersion !== null;
            })
            ->sortBy('name_current')
            ->values();
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    private function categories(): Collection
    {
        return ProductCategory::query()
            ->where('is_active', true)
            ->whereHas('versions.product', fn ($query) => $query->publicCatalog())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, ProductTag>
     */
    private function tags(): Collection
    {
        return ProductTag::query()
            ->whereHas('versions.product', fn ($query) => $query->publicCatalog())
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function seoPayload(Product $product, ProductVersion $version, bool $isPreview): array
    {
        $seoMeta = $version->seoMeta;
        $descriptionSource = $seoMeta?->meta_description
            ?? $version->short_description
            ?? $version->full_description
            ?? $version->rich_body;

        $seo = [
            'meta_title' => $seoMeta?->meta_title ?? $version->name.' | Products | '.config('app.name', 'USNsoft'),
            'meta_description' => Str::limit(trim(strip_tags((string) $descriptionSource)), 160),
            'canonical_url' => $isPreview
                ? url()->current()
                : route('products.show', ['product' => $product->slug_current]),
            'og_title' => $seoMeta?->og_title ?? $version->name.' | '.config('app.name', 'USNsoft'),
            'og_description' => Str::limit(trim(strip_tags((string) ($seoMeta?->og_description ?? $descriptionSource))), 200),
            'robots_index' => $isPreview
                ? false
                : (($seoMeta?->robots_index ?? true) && $version->product_visibility !== ProductVisibility::Unlisted),
            'robots_follow' => $isPreview ? false : ($seoMeta?->robots_follow ?? true),
        ];

        $ogImage = $seoMeta?->ogImage ?? $version->featuredImage ?? $product->featuredImage;

        if ($ogImage && $ogImage->disk === 'public') {
            $seo['og_image_url'] = asset('storage/'.$ogImage->path);
        }

        return $seo;
    }

    private function videoEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);
        $host = Str::lower((string) ($parsed['host'] ?? ''));
        $path = (string) ($parsed['path'] ?? '');

        parse_str((string) ($parsed['query'] ?? ''), $query);

        return match (true) {
            str_contains($host, 'youtube.com') && isset($query['v']) => 'https://www.youtube.com/embed/'.rawurlencode((string) $query['v']),
            str_contains($host, 'youtu.be') && $path !== '' => 'https://www.youtube.com/embed/'.rawurlencode(ltrim($path, '/')),
            str_contains($host, 'vimeo.com') && $path !== '' => 'https://player.vimeo.com/video/'.rawurlencode(ltrim($path, '/')),
            str_contains($host, 'loom.com') && str_contains($path, '/share/') => 'https://www.loom.com/embed/'.rawurlencode(Str::after($path, '/share/')),
            default => null,
        };
    }
}
