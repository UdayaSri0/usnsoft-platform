<?php

namespace App\Modules\Showcase\Controllers\Admin;

use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentWorkflowActionRequest;
use App\Services\Audit\AuditLogService;
use App\Services\Content\ContentSanitizerService;
use App\Services\Content\DirectContentWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class AbstractShowcaseController extends Controller
{
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ContentSanitizerService $contentSanitizer,
        protected readonly DirectContentWorkflowService $workflowService,
    ) {}

    public function index(Request $request): View
    {
        $modelClass = $this->modelClass();
        $this->authorize('viewAny', $modelClass);

        $status = $request->string('status')->toString();
        $featured = $request->string('featured')->toString();
        $q = $request->string('q')->toString();

        $items = $modelClass::query()
            ->when($q !== '', fn (Builder $query) => $this->applySearch($query, $q))
            ->when($status !== '', fn (Builder $query) => $query->where('workflow_state', $status))
            ->when($featured !== '', fn (Builder $query) => $query->where('featured_flag', filter_var($featured, FILTER_VALIDATE_BOOL)))
            ->orderByDesc('featured_flag')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.showcase.index', [
            'items' => $items,
            'resourceKey' => $this->resourceKey(),
            'resourceLabel' => $this->resourceLabel(),
            'routeBase' => $this->routeBase(),
            'filters' => compact('status', 'featured', 'q'),
            'workflowStates' => ContentWorkflowState::cases(),
        ]);
    }

    public function create(): View
    {
        $modelClass = $this->modelClass();
        $this->authorize('create', $modelClass);

        return view('admin.showcase.form', $this->formViewData());
    }

    public function store(Request $request): RedirectResponse
    {
        $modelClass = $this->modelClass();
        $this->authorize('create', $modelClass);

        $item = new $modelClass;
        $validated = $request->validate($this->rules($item));
        $this->persist($item, $request->user(), $validated);

        return redirect()
            ->route($this->routeBase().'.edit', ['item' => $item->getKey()])
            ->with('status', $this->resourceKey().'-created');
    }

    public function edit(string $item): View
    {
        $item = $this->resolveItem($item);
        $this->authorize('view', $item);

        return view('admin.showcase.form', $this->formViewData($item));
    }

    public function update(Request $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('update', $item);

        $validated = $request->validate($this->rules($item));
        $this->persist($item, $request->user(), $validated);

        return redirect()
            ->route($this->routeBase().'.edit', ['item' => $item->getKey()])
            ->with('status', $this->resourceKey().'-updated');
    }

    public function submitForReview(ContentWorkflowActionRequest $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('submitForReview', $item);

        $this->workflowService->submitForReview($item, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', $this->resourceKey().'-submitted');
    }

    public function approve(ContentWorkflowActionRequest $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('approve', $item);

        $this->workflowService->approve($item, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', $this->resourceKey().'-approved');
    }

    public function reject(ContentWorkflowActionRequest $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('reject', $item);

        $this->workflowService->reject($item, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', $this->resourceKey().'-rejected');
    }

    public function schedule(ContentWorkflowActionRequest $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('schedule', $item);

        $publishAt = CarbonImmutable::parse($request->string('schedule_publish_at')->toString());
        $unpublishInput = $request->string('schedule_unpublish_at')->toString();
        $unpublishAt = $unpublishInput !== '' ? CarbonImmutable::parse($unpublishInput) : null;

        $this->workflowService->schedulePublish(
            content: $item,
            actor: $request->user(),
            publishAt: $publishAt,
            notes: $request->string('notes')->toString() ?: null,
            unpublishAt: $unpublishAt,
        );

        return back()->with('status', $this->resourceKey().'-scheduled');
    }

    public function publish(ContentWorkflowActionRequest $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('publish', $item);

        $this->workflowService->publishNow($item, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', $this->resourceKey().'-published');
    }

    public function archive(ContentWorkflowActionRequest $request, string $item): RedirectResponse
    {
        $item = $this->resolveItem($item);
        $this->authorize('archive', $item);

        $this->workflowService->archive($item, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', $this->resourceKey().'-archived');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formViewData(?Model $item = null): array
    {
        return [
            'item' => $item,
            'resourceKey' => $this->resourceKey(),
            'resourceLabel' => $this->resourceLabel(),
            'routeBase' => $this->routeBase(),
        ];
    }

    abstract protected function applySearch(Builder $query, string $term): Builder;

    abstract protected function modelClass(): string;

    abstract protected function persist(Model $item, \App\Models\User $actor, array $validated): void;

    /**
     * @param  Model  $item
     * @return array<string, mixed>
     */
    abstract protected function rules(Model $item): array;

    abstract protected function resourceKey(): string;

    abstract protected function resourceLabel(): string;

    abstract protected function routeBase(): string;

    /**
     * @return array<string, mixed>
     */
    protected function sharedRules(): array
    {
        return [
            'featured_flag' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'visibility' => ['required', \Illuminate\Validation\Rule::in(VisibilityState::values())],
            'change_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function resolveItem(string $item): Model
    {
        $modelClass = $this->modelClass();

        return $modelClass::query()->findOrFail($item);
    }
}
