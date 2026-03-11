<?php

namespace App\Modules\Pages\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Enums\BlockEditorMode;
use App\Modules\Pages\Models\BlockDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlockDefinitionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BlockDefinition::class);

        $definitions = BlockDefinition::query()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.cms.block-definitions.index', [
            'definitions' => $definitions,
            'editorModes' => BlockEditorMode::cases(),
        ]);
    }

    public function update(Request $request, BlockDefinition $blockDefinition): RedirectResponse
    {
        $this->authorize('update', $blockDefinition);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'editor_mode' => ['required', Rule::in(BlockEditorMode::values())],
            'is_active' => ['required', 'boolean'],
            'is_reusable_allowed' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:5000'],
        ]);

        $blockDefinition->forceFill([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'editor_mode' => $validated['editor_mode'],
            'is_active' => (bool) $validated['is_active'],
            'is_reusable_allowed' => (bool) $validated['is_reusable_allowed'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ])->save();

        return redirect()->route('admin.cms.block-definitions.index')->with('status', 'cms-block-definition-updated');
    }
}
