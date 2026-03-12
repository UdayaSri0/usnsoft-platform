<?php

namespace App\Modules\Blog\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\BlogCategory;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Blog\Models\BlogTag;
use App\Modules\Blog\Services\BlogCatalogService;
use App\Services\Content\StructuredContentBlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogCatalogService $catalogService,
        private readonly StructuredContentBlockService $structuredContentBlockService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->toString(),
            'category' => $request->string('category')->toString(),
            'tag' => $request->string('tag')->toString(),
            'featured' => $request->boolean('featured'),
        ];

        return view('blog.index', [
            'posts' => $this->catalogService->publicListing($filters),
            'filters' => $filters,
            'categories' => BlogCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => BlogTag::query()->orderBy('name')->get(),
            'seo' => [
                'meta_title' => 'Blog & News | '.config('app.name', 'USNsoft'),
                'meta_description' => 'News, insights, engineering notes, and platform updates from USNsoft.',
                'canonical_url' => route('blog.index'),
                'og_title' => 'Blog & News | '.config('app.name', 'USNsoft'),
                'og_description' => 'Read the latest USNsoft updates, platform notes, and delivery insights.',
            ],
        ]);
    }

    public function show(BlogPost $post): View
    {
        $resolved = $this->catalogService->resolvePublicPost($post->slug);

        abort_unless($resolved, 404);

        $resolved->loadMissing(['seoMeta.ogImage', 'featuredImage']);

        $descriptionSource = $resolved->seoMeta?->meta_description
            ?? $resolved->excerpt
            ?? ($resolved->content_blocks_json[0]['data']['content_html'] ?? null);

        $seo = [
            'meta_title' => $resolved->seoMeta?->meta_title ?? $resolved->title.' | '.config('app.name', 'USNsoft'),
            'meta_description' => Str::limit(trim(strip_tags((string) $descriptionSource)), 160),
            'canonical_url' => route('blog.show', ['post' => $resolved->slug]),
            'og_title' => $resolved->seoMeta?->og_title ?? $resolved->title.' | '.config('app.name', 'USNsoft'),
            'og_description' => Str::limit(trim(strip_tags((string) ($resolved->seoMeta?->og_description ?? $descriptionSource))), 200),
            'robots_index' => $resolved->seoMeta?->robots_index ?? true,
            'robots_follow' => $resolved->seoMeta?->robots_follow ?? true,
        ];

        $ogImage = $resolved->seoMeta?->ogImage ?? $resolved->featuredImage;
        if ($ogImage && $ogImage->disk === 'public') {
            $seo['og_image_url'] = asset('storage/'.$ogImage->path);
        }

        return view('blog.show', [
            'post' => $resolved,
            'blocks' => $this->structuredContentBlockService->renderableBlocks($resolved->content_blocks_json ?? []),
            'relatedPosts' => $this->catalogService->related($resolved, 3),
            'seo' => $seo,
        ]);
    }
}
