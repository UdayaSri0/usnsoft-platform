<?php

namespace App\Modules\Products\Controllers\Admin;

use App\Enums\ContentWorkflowState;
use App\Http\Controllers\Controller;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductKind;
use App\Modules\Products\Enums\ProductPlatform;
use App\Modules\Products\Enums\ProductPricingMode;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductCategory;
use App\Modules\Products\Models\ProductTag;
use App\Modules\Products\Models\ProductVersion;
use App\Modules\Products\Requests\ProductStoreRequest;
use App\Modules\Products\Requests\ProductUpdateRequest;
use App\Modules\Products\Requests\ProductWorkflowActionRequest;
use App\Modules\Products\Services\ProductPreviewTokenService;
use App\Modules\Products\Services\ProductWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductPreviewTokenService $previewTokenService,
        private readonly ProductWorkflowService $workflowService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $status = $request->string('status')->toString();
        $visibility = $request->string('visibility')->toString();
        $category = $request->string('category')->toString();
        $featured = $request->string('featured')->toString();
        $q = $request->string('q')->toString();

        $products = Product::query()
            ->with([
                'currentDraftVersion.category',
                'currentPublishedVersion.category',
            ])
            ->when($q !== '', fn ($query) => $query->search($q))
            ->when($visibility !== '', fn ($query) => $query->where('visibility', $visibility))
            ->when($category !== '', function ($query) use ($category): void {
                $query->whereHas('versions.category', fn ($categoryQuery) => $categoryQuery->where('slug', $category));
            })
            ->when($featured !== '', fn ($query) => $query->where('featured_flag', filter_var($featured, FILTER_VALIDATE_BOOL)))
            ->when($status !== '', function ($query) use ($status): void {
                if ($status === ContentWorkflowState::Published->value) {
                    $query->whereHas('currentPublishedVersion', fn ($versionQuery) => $versionQuery->where('workflow_state', $status));

                    return;
                }

                if ($status === ContentWorkflowState::Archived->value) {
                    $query->whereHas('versions', fn ($versionQuery) => $versionQuery->where('workflow_state', $status));

                    return;
                }

                $query->whereHas('currentDraftVersion', fn ($versionQuery) => $versionQuery->where('workflow_state', $status));
            })
            ->orderByDesc('featured_flag')
            ->orderBy('name_current')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => ProductCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'filters' => compact('status', 'visibility', 'category', 'featured', 'q'),
            'workflowStates' => ContentWorkflowState::cases(),
            'visibilityStates' => ProductVisibility::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', $this->editorViewData());
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->workflowService->createProductWithDraft(
            actor: $request->user(),
            productAttributes: [],
            versionAttributes: $request->validated(),
        );

        return redirect()
            ->route('admin.products.edit', ['product' => $product->getKey()])
            ->with('status', 'product-created');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorize('view', $product);

        $draft = $this->workflowService->ensureDraft($product, $request->user())->fresh([
            'category',
            'tags',
            'platforms',
            'allFaqItems',
            'screenshots.mediaAsset',
            'downloads.mediaAsset',
            'relatedProducts',
            'seoMeta.ogImage',
            'featuredImage',
        ]);

        $product->refresh()->load([
            'currentPublishedVersion.category',
            'currentPublishedVersion.tags',
            'currentPublishedVersion.platforms',
        ]);

        return view('admin.products.edit', array_merge($this->editorViewData($product), [
            'product' => $product,
            'draft' => $draft,
            'publishedVersion' => $product->currentPublishedVersion,
        ]));
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $draft = $this->workflowService->ensureDraft($product, $request->user());
        $this->workflowService->updateDraftContent($draft, $request->user(), $request->validated());

        return redirect()
            ->route('admin.products.edit', ['product' => $product->getKey()])
            ->with('status', 'product-draft-updated');
    }

    public function submitForReview(ProductWorkflowActionRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('submitForReview', $product);

        $draft = $this->workflowService->ensureDraft($product, $request->user());
        $this->workflowService->submitForReview($draft, $request->user(), $request->string('notes')->toString() ?: null);

        return redirect()
            ->route('admin.products.edit', ['product' => $product->getKey()])
            ->with('status', 'product-submitted-for-review');
    }

    public function approve(ProductWorkflowActionRequest $request, ProductVersion $version): RedirectResponse
    {
        $this->authorize('approve', $version->product);

        $this->workflowService->approve($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'product-version-approved');
    }

    public function reject(ProductWorkflowActionRequest $request, ProductVersion $version): RedirectResponse
    {
        $this->authorize('reject', $version->product);

        $this->workflowService->reject($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'product-version-rejected');
    }

    public function schedule(ProductWorkflowActionRequest $request, ProductVersion $version): RedirectResponse
    {
        $this->authorize('schedule', $version->product);

        $publishAt = CarbonImmutable::parse($request->string('schedule_publish_at')->toString());
        $unpublishInput = $request->string('schedule_unpublish_at')->toString();
        $unpublishAt = $unpublishInput !== '' ? CarbonImmutable::parse($unpublishInput) : null;

        $this->workflowService->schedulePublish(
            version: $version,
            actor: $request->user(),
            publishAt: $publishAt,
            notes: $request->string('notes')->toString() ?: null,
            unpublishAt: $unpublishAt,
        );

        return back()->with('status', 'product-version-scheduled');
    }

    public function publish(ProductWorkflowActionRequest $request, ProductVersion $version): RedirectResponse
    {
        $this->authorize('publish', $version->product);

        if ($request->boolean('preview_confirmed')) {
            $this->workflowService->confirmPreview($version, $request->user());
        }

        $this->workflowService->publishNow($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'product-version-published');
    }

    public function archive(ProductWorkflowActionRequest $request, ProductVersion $version): RedirectResponse
    {
        $this->authorize('archive', $version->product);

        $this->workflowService->archive($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'product-version-archived');
    }

    public function preview(Request $request, ProductVersion $version): RedirectResponse
    {
        $this->authorize('preview', $version);

        $token = $this->previewTokenService->issue($version, $request->user());

        return redirect()
            ->route('admin.products.edit', ['product' => $version->product_id])
            ->with('status', 'product-preview-link-generated')
            ->with('preview_url', route('products.preview.show', [
                'version' => $version->getKey(),
                'token' => $token,
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function editorViewData(?Product $product = null): array
    {
        return [
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => ProductTag::query()->orderBy('name')->get(),
            'relatedProducts' => Product::query()
                ->when($product, fn ($query) => $query->whereKeyNot($product->getKey()))
                ->orderBy('name_current')
                ->get(),
            'mediaAssets' => MediaAsset::query()->orderByDesc('created_at')->limit(200)->get(),
            'productKinds' => ProductKind::cases(),
            'pricingModes' => ProductPricingMode::cases(),
            'downloadModes' => ProductDownloadMode::cases(),
            'downloadVisibilities' => ProductDownloadVisibility::cases(),
            'productVisibilities' => ProductVisibility::cases(),
            'platforms' => ProductPlatform::cases(),
        ];
    }
}
