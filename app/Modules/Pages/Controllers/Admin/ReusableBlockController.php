<?php

namespace App\Modules\Pages\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\BlockDefinition;
use App\Modules\Pages\Models\ReusableBlock;
use App\Modules\Pages\Requests\ReusableBlockRequest;
use App\Modules\Pages\Services\BlockValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReusableBlockController extends Controller
{
    public function __construct(
        private readonly BlockValidationService $blockValidationService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ReusableBlock::class);

        $blocks = ReusableBlock::query()
            ->with('blockDefinition')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.cms.reusable-blocks.index', [
            'blocks' => $blocks,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ReusableBlock::class);

        return view('admin.cms.reusable-blocks.create', [
            'definitions' => BlockDefinition::query()->where('is_active', true)->where('is_reusable_allowed', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ReusableBlockRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $definition = BlockDefinition::query()->findOrFail($validated['block_definition_id']);

        $payload = is_array($validated['data'] ?? null) ? $validated['data'] : [];
        $jsonPayload = trim((string) ($validated['data_json'] ?? ''));

        if ($jsonPayload !== '') {
            $decoded = json_decode($jsonPayload, true);

            if (is_array($decoded)) {
                $payload = array_replace_recursive($payload, $decoded);
            }
        }

        $data = $this->blockValidationService->validateAndNormalize(
            $definition->key,
            $payload,
        );

        $block = ReusableBlock::query()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'block_definition_id' => $definition->getKey(),
            'workflow_state' => \App\Enums\ContentWorkflowState::Draft,
            'approval_state' => \App\Enums\ApprovalState::Draft,
            'data_json' => $data,
            'layout_json' => is_array($validated['layout'] ?? null) ? $validated['layout'] : [],
            'visibility_json' => is_array($validated['visibility'] ?? null) ? $validated['visibility'] : [],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()->route('admin.cms.reusable-blocks.edit', $block)->with('status', 'cms-reusable-block-created');
    }

    public function edit(Request $request, ReusableBlock $reusableBlock): View
    {
        $this->authorize('view', $reusableBlock);

        return view('admin.cms.reusable-blocks.edit', [
            'block' => $reusableBlock->load('blockDefinition'),
            'definitions' => BlockDefinition::query()->where('is_active', true)->where('is_reusable_allowed', true)->orderBy('name')->get(),
        ]);
    }

    public function update(ReusableBlockRequest $request, ReusableBlock $reusableBlock): RedirectResponse
    {
        $this->authorize('update', $reusableBlock);

        $validated = $request->validated();
        $definition = BlockDefinition::query()->findOrFail($validated['block_definition_id']);

        $payload = is_array($validated['data'] ?? null) ? $validated['data'] : [];
        $jsonPayload = trim((string) ($validated['data_json'] ?? ''));

        if ($jsonPayload !== '') {
            $decoded = json_decode($jsonPayload, true);

            if (is_array($decoded)) {
                $payload = array_replace_recursive($payload, $decoded);
            }
        }

        $data = $this->blockValidationService->validateAndNormalize(
            $definition->key,
            $payload,
        );

        $reusableBlock->forceFill([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'block_definition_id' => $definition->getKey(),
            'data_json' => $data,
            'layout_json' => is_array($validated['layout'] ?? null) ? $validated['layout'] : [],
            'visibility_json' => is_array($validated['visibility'] ?? null) ? $validated['visibility'] : [],
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->getKey(),
        ])->save();

        return redirect()->route('admin.cms.reusable-blocks.edit', $reusableBlock)->with('status', 'cms-reusable-block-updated');
    }
}
