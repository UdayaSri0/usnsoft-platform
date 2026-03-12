<?php

namespace App\Services\Content;

use App\Enums\ApprovalState;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Pages\Enums\BlockEditorMode;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Pages\Models\BlockDefinition;
use App\Modules\Pages\Models\ReusableBlock;
use App\Modules\Pages\Services\BlockValidationService;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StructuredContentBlockService
{
    public function __construct(
        private readonly BlockValidationService $blockValidationService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $rawBlocks
     * @return array<int, array<string, mixed>>
     */
    public function normalizeForStorage(array $rawBlocks, User $actor): array
    {
        $normalized = [];

        foreach ($rawBlocks as $index => $rawBlock) {
            $blockType = trim((string) ($rawBlock['block_type'] ?? ''));

            if ($blockType === '') {
                continue;
            }

            $definition = BlockDefinition::query()->where('key', $blockType)->first();

            if (! $definition || ! $definition->is_active) {
                throw ValidationException::withMessages([
                    'blocks' => "Block [{$blockType}] is not available.",
                ]);
            }

            if ($definition->editor_mode === BlockEditorMode::SuperAdminOnly && ! $actor->hasRole(CoreRole::SuperAdmin)) {
                throw ValidationException::withMessages([
                    'blocks' => "Block [{$definition->name}] is restricted to SuperAdmin users.",
                ]);
            }

            if (
                $definition->editor_mode === BlockEditorMode::Advanced
                && ! $actor->hasRole(CoreRole::SuperAdmin)
                && ! $actor->hasPermission(CmsPermission::PagesUseAdvancedBlocks->value)
            ) {
                throw ValidationException::withMessages([
                    'blocks' => "You do not have permission to use advanced block [{$definition->name}].",
                ]);
            }

            $reusableBlockId = $rawBlock['reusable_block_id'] ?? null;
            $reusableBlock = null;

            if ($reusableBlockId) {
                $reusableBlock = ReusableBlock::query()->find($reusableBlockId);

                if (! $reusableBlock) {
                    throw ValidationException::withMessages([
                        'blocks' => "Reusable block [{$reusableBlockId}] does not exist.",
                    ]);
                }

                if (! $actor->hasRole(CoreRole::SuperAdmin) && ! $actor->hasPermission(CmsPermission::PagesUseReusableBlocks->value)) {
                    throw ValidationException::withMessages([
                        'blocks' => 'You do not have permission to use reusable blocks.',
                    ]);
                }

                if (
                    ! $actor->hasRole(CoreRole::SuperAdmin)
                    && (
                        $reusableBlock->workflow_state !== \App\Enums\ContentWorkflowState::Published
                        || $reusableBlock->approval_state !== ApprovalState::Approved
                    )
                ) {
                    throw ValidationException::withMessages([
                        'blocks' => "Reusable block [{$reusableBlock->name}] is not approved for use yet.",
                    ]);
                }
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
                'block_type' => $definition->key,
                'reusable_block_id' => $reusableBlock?->getKey(),
                'region_key' => Arr::get($rawBlock, 'region_key', 'main'),
                'sort_order' => (int) Arr::get($rawBlock, 'sort_order', $index + 1),
                'internal_name' => Arr::get($rawBlock, 'internal_name'),
                'is_enabled' => filter_var(Arr::get($rawBlock, 'is_enabled', true), FILTER_VALIDATE_BOOL),
                'visibility' => is_array($rawBlock['visibility'] ?? null) ? $rawBlock['visibility'] : [],
                'layout' => is_array($rawBlock['layout'] ?? null) ? $rawBlock['layout'] : [],
                'data' => $this->blockValidationService->validateAndNormalize($definition->key, $data),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function renderableBlocks(array $blocks): array
    {
        $definitionKeys = collect($blocks)
            ->map(fn (array $block): string => (string) ($block['block_type'] ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $reusableIds = collect($blocks)
            ->map(fn (array $block): mixed => $block['reusable_block_id'] ?? null)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $definitions = BlockDefinition::query()
            ->whereIn('key', $definitionKeys)
            ->where('is_active', true)
            ->get()
            ->keyBy('key');

        $reusableBlocks = ReusableBlock::query()
            ->whereIn('id', $reusableIds)
            ->get()
            ->keyBy('id');

        return collect($blocks)
            ->filter(static fn (array $block): bool => (bool) ($block['is_enabled'] ?? true))
            ->map(function (array $block) use ($definitions, $reusableBlocks): ?array {
                $definition = $definitions->get((string) ($block['block_type'] ?? ''));

                if (! $definition) {
                    return null;
                }

                $reusableBlock = $reusableBlocks->get((int) ($block['reusable_block_id'] ?? 0));
                $blockData = $reusableBlock?->data_json ?? $block['data'] ?? [];
                $blockLayout = array_replace(
                    $definition->default_layout_json ?? [],
                    $reusableBlock?->layout_json ?? [],
                    is_array($block['layout'] ?? null) ? $block['layout'] : [],
                );
                $visibility = array_replace(
                    ['desktop' => true, 'tablet' => true, 'mobile' => true],
                    $reusableBlock?->visibility_json ?? [],
                    is_array($block['visibility'] ?? null) ? $block['visibility'] : [],
                );

                return [
                    'internal_name' => $block['internal_name'] ?? null,
                    'type' => $definition->key,
                    'view' => $definition->rendering_view,
                    'data' => is_array($blockData) ? $blockData : [],
                    'layout' => is_array($blockLayout) ? $blockLayout : [],
                    'visibility' => is_array($visibility) ? $visibility : [],
                    'meta' => [
                        'definition_id' => $definition->getKey(),
                        'reusable_block_id' => $reusableBlock?->getKey(),
                        'category' => $definition->category,
                    ],
                ];
            })
            ->filter(static fn (?array $block): bool => $block !== null)
            ->values()
            ->all();
    }
}
