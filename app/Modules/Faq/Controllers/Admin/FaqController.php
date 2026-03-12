<?php

namespace App\Modules\Faq\Controllers\Admin;

use App\Enums\ContentWorkflowState;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentWorkflowActionRequest;
use App\Modules\Faq\Models\Faq;
use App\Modules\Faq\Models\FaqCategory;
use App\Modules\Faq\Requests\FaqStoreRequest;
use App\Modules\Faq\Services\FaqContentService;
use App\Modules\Products\Models\Product;
use App\Services\Content\DirectContentWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqContentService $faqContentService,
        private readonly DirectContentWorkflowService $workflowService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Faq::class);

        $status = $request->string('status')->toString();
        $category = $request->string('category')->toString();
        $q = $request->string('q')->toString();

        $faqs = Faq::query()
            ->with(['category', 'linkedProduct'])
            ->search($q)
            ->when($status !== '', fn ($query) => $query->where('workflow_state', $status))
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->paginate(25)
            ->withQueryString();

        return view('admin.faq.index', [
            'faqs' => $faqs,
            'categories' => FaqCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name_current')->get(),
            'filters' => compact('status', 'category', 'q'),
            'workflowStates' => ContentWorkflowState::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Faq::class);

        return view('admin.faq.create', [
            'faq' => null,
            'categories' => FaqCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name_current')->get(),
        ]);
    }

    public function store(FaqStoreRequest $request): RedirectResponse
    {
        $faq = $this->faqContentService->store(new Faq, $request->user(), $request->validated());

        return redirect()
            ->route('admin.faq.edit', ['faq' => $faq->getKey()])
            ->with('status', 'faq-created');
    }

    public function edit(Faq $faq): View
    {
        $this->authorize('view', $faq);

        return view('admin.faq.edit', [
            'faq' => $faq->load(['category', 'linkedProduct']),
            'categories' => FaqCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name_current')->get(),
        ]);
    }

    public function update(FaqStoreRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('update', $faq);

        $this->faqContentService->store($faq, $request->user(), $request->validated());

        return redirect()
            ->route('admin.faq.edit', ['faq' => $faq->getKey()])
            ->with('status', 'faq-updated');
    }

    public function submitForReview(ContentWorkflowActionRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('submitForReview', $faq);

        $this->workflowService->submitForReview($faq, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'faq-submitted');
    }

    public function approve(ContentWorkflowActionRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('approve', $faq);

        $this->workflowService->approve($faq, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'faq-approved');
    }

    public function reject(ContentWorkflowActionRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('reject', $faq);

        $this->workflowService->reject($faq, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'faq-rejected');
    }

    public function schedule(ContentWorkflowActionRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('schedule', $faq);

        $publishAt = CarbonImmutable::parse($request->string('schedule_publish_at')->toString());
        $unpublishInput = $request->string('schedule_unpublish_at')->toString();
        $unpublishAt = $unpublishInput !== '' ? CarbonImmutable::parse($unpublishInput) : null;

        $this->workflowService->schedulePublish(
            content: $faq,
            actor: $request->user(),
            publishAt: $publishAt,
            notes: $request->string('notes')->toString() ?: null,
            unpublishAt: $unpublishAt,
        );

        return back()->with('status', 'faq-scheduled');
    }

    public function publish(ContentWorkflowActionRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('publish', $faq);

        $this->workflowService->publishNow($faq, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'faq-published');
    }

    public function archive(ContentWorkflowActionRequest $request, Faq $faq): RedirectResponse
    {
        $this->authorize('archive', $faq);

        $this->workflowService->archive($faq, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'faq-archived');
    }
}
