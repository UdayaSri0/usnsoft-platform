<?php

namespace App\Modules\Pages\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\BlockDefinition;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Models\ReusableBlock;
use App\Modules\Pages\Requests\PageStoreRequest;
use App\Modules\Pages\Requests\PageUpdateRequest;
use App\Modules\Pages\Requests\PageWorkflowActionRequest;
use App\Modules\Pages\Services\CmsWorkflowService;
use App\Modules\Pages\Services\PageRenderService;
use App\Modules\Pages\Services\PreviewTokenService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly CmsWorkflowService $cmsWorkflowService,
        private readonly PageRenderService $pageRenderService,
        private readonly PreviewTokenService $previewTokenService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Page::class);

        $pages = Page::query()
            ->with(['currentDraftVersion', 'currentPublishedVersion'])
            ->orderBy('is_home', 'desc')
            ->orderBy('is_system_page', 'desc')
            ->orderBy('title_current')
            ->paginate(20);

        return view('admin.cms.pages.index', [
            'pages' => $pages,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Page::class);

        return view('admin.cms.pages.create', [
            'definitions' => BlockDefinition::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'approvedReusableBlocks' => ReusableBlock::query()
                ->where('workflow_state', \App\Enums\ContentWorkflowState::Published->value)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(PageStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $path = $this->pageRenderService->normalizePath((string) ($validated['path'] ?? $validated['slug']));

        $page = $this->cmsWorkflowService->createPageWithDraft(
            actor: $request->user(),
            pageAttributes: [
                'key' => $validated['key'] ?? null,
                'page_type' => $validated['page_type'],
                'is_home' => (bool) ($validated['is_home'] ?? false),
                'is_system_page' => (bool) ($validated['is_system_page'] ?? false),
                'is_locked_slug' => (bool) ($validated['is_locked_slug'] ?? false),
            ],
            versionAttributes: [
                'title' => $validated['title'],
                'slug' => trim((string) $validated['slug'], '/'),
                'path' => $path,
                'summary' => $validated['summary'] ?? null,
                'change_notes' => $validated['change_notes'] ?? null,
                'layout_settings_json' => $validated['layout_settings_json'] ?? null,
                'seo_snapshot_json' => $validated['seo_snapshot_json'] ?? null,
            ],
            blocks: $this->normalizeBlocks(is_array($validated['blocks'] ?? null) ? $validated['blocks'] : []),
        );

        return redirect()
            ->route('admin.cms.pages.edit', $page)
            ->with('status', 'cms-page-created');
    }

    public function edit(Request $request, Page $page): View
    {
        $this->authorize('view', $page);

        $draft = $this->cmsWorkflowService->ensureDraft($page, $request->user());

        $page->refresh()->load([
            'currentPublishedVersion',
            'currentDraftVersion.blocks.blockDefinition',
            'currentDraftVersion.blocks.reusableBlock',
        ]);

        return view('admin.cms.pages.edit', [
            'page' => $page,
            'draft' => $draft->fresh(['blocks.blockDefinition', 'blocks.reusableBlock']),
            'publishedVersion' => $page->currentPublishedVersion,
            'definitions' => BlockDefinition::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'approvedReusableBlocks' => ReusableBlock::query()
                ->where('workflow_state', \App\Enums\ContentWorkflowState::Published->value)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(PageUpdateRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        $draft = $this->cmsWorkflowService->ensureDraft($page, $request->user());
        $validated = $request->validated();

        $this->cmsWorkflowService->updateDraftContent(
            draft: $draft,
            actor: $request->user(),
            versionAttributes: [
                'title' => $validated['title'],
                'slug' => trim((string) $validated['slug'], '/'),
                'path' => $this->pageRenderService->normalizePath((string) ($validated['path'] ?? $validated['slug'])),
                'summary' => $validated['summary'] ?? null,
                'change_notes' => $validated['change_notes'] ?? null,
                'layout_settings_json' => $validated['layout_settings_json'] ?? null,
                'seo_snapshot_json' => $validated['seo_snapshot_json'] ?? null,
            ],
            blocks: $this->normalizeBlocks(is_array($validated['blocks'] ?? null) ? $validated['blocks'] : []),
        );

        return redirect()
            ->route('admin.cms.pages.edit', $page)
            ->with('status', 'cms-draft-updated');
    }

    public function submitForReview(PageWorkflowActionRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('submitForReview', $page);

        $draft = $this->cmsWorkflowService->ensureDraft($page, $request->user());
        $this->cmsWorkflowService->submitForReview($draft, $request->user(), $request->string('notes')->toString() ?: null);

        return redirect()->route('admin.cms.pages.edit', $page)->with('status', 'cms-submitted-for-review');
    }

    public function approve(PageWorkflowActionRequest $request, PageVersion $version): RedirectResponse
    {
        $this->authorize('approve', $version->page);

        $this->cmsWorkflowService->approve($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'cms-version-approved');
    }

    public function reject(PageWorkflowActionRequest $request, PageVersion $version): RedirectResponse
    {
        $this->authorize('reject', $version->page);

        $this->cmsWorkflowService->reject($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'cms-version-rejected');
    }

    public function schedule(PageWorkflowActionRequest $request, PageVersion $version): RedirectResponse
    {
        $this->authorize('schedule', $version->page);

        $publishAt = CarbonImmutable::parse($request->string('schedule_publish_at')->toString());
        $unpublishInput = $request->string('schedule_unpublish_at')->toString();
        $unpublishAt = $unpublishInput !== '' ? CarbonImmutable::parse($unpublishInput) : null;

        $this->cmsWorkflowService->schedulePublish(
            version: $version,
            actor: $request->user(),
            publishAt: $publishAt,
            notes: $request->string('notes')->toString() ?: null,
            unpublishAt: $unpublishAt,
        );

        return back()->with('status', 'cms-version-scheduled');
    }

    public function publish(PageWorkflowActionRequest $request, PageVersion $version): RedirectResponse
    {
        $this->authorize('publish', $version->page);

        if ($request->boolean('preview_confirmed')) {
            $this->cmsWorkflowService->confirmPreview($version, $request->user());
        }

        $this->cmsWorkflowService->publishNow($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'cms-version-published');
    }

    public function archive(PageWorkflowActionRequest $request, PageVersion $version): RedirectResponse
    {
        $this->authorize('archive', $version->page);

        $this->cmsWorkflowService->archive($version, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'cms-version-archived');
    }

    public function preview(Request $request, PageVersion $version): RedirectResponse
    {
        $this->authorize('preview', $version);

        $token = $this->previewTokenService->issue($version, $request->user());

        return redirect()
            ->route('admin.cms.pages.edit', $version->page)
            ->with('status', 'cms-preview-link-generated')
            ->with('preview_url', route('cms.preview.show', ['version' => $version, 'token' => $token]));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawBlocks
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBlocks(array $rawBlocks): array
    {
        $normalized = [];

        foreach ($rawBlocks as $rawBlock) {
            $blockType = trim((string) ($rawBlock['block_type'] ?? ''));

            if ($blockType === '') {
                continue;
            }

            $data = is_array($rawBlock['data'] ?? null) ? $rawBlock['data'] : [];
            $jsonData = trim((string) ($rawBlock['data_json'] ?? ''));

            if ($jsonData !== '') {
                $decoded = json_decode($jsonData, true);

                if (is_array($decoded)) {
                    $data = array_replace_recursive($data, $decoded);
                }
            }

            $normalized[] = [
                'block_type' => $blockType,
                'reusable_block_id' => Arr::get($rawBlock, 'reusable_block_id'),
                'region_key' => Arr::get($rawBlock, 'region_key'),
                'sort_order' => Arr::get($rawBlock, 'sort_order'),
                'internal_name' => Arr::get($rawBlock, 'internal_name'),
                'is_enabled' => filter_var(Arr::get($rawBlock, 'is_enabled', true), FILTER_VALIDATE_BOOL),
                'visibility' => is_array($rawBlock['visibility'] ?? null) ? $rawBlock['visibility'] : [],
                'layout' => is_array($rawBlock['layout'] ?? null) ? $rawBlock['layout'] : [],
                'data' => $data,
            ];
        }

        return $normalized;
    }
}
