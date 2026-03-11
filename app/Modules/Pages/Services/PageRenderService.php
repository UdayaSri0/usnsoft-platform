<?php

namespace App\Modules\Pages\Services;

use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageVersion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PageRenderService
{
    public function resolvePublishedVersion(string $path): ?PageVersion
    {
        if (! Schema::hasTable('pages')) {
            return null;
        }

        $normalizedPath = $this->normalizePath($path);

        $page = Page::query()
            ->where('path_current', $normalizedPath)
            ->where('is_active', true)
            ->whereNotNull('current_published_version_id')
            ->with([
                'currentPublishedVersion.blocks.blockDefinition',
                'currentPublishedVersion.blocks.reusableBlock.blockDefinition',
            ])
            ->first();

        return $page?->currentPublishedVersion;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function renderableBlocks(PageVersion $version): array
    {
        $cacheKey = $this->cacheKey($version);

        if ((bool) config('cms.cache.enabled', true)) {
            $ttl = now()->addMinutes((int) config('cms.cache.ttl_minutes', 15));

            return Cache::remember($cacheKey, $ttl, fn (): array => $this->buildRenderableBlocks($version));
        }

        return $this->buildRenderableBlocks($version);
    }

    public function flushVersionCache(PageVersion $version): void
    {
        Cache::forget($this->cacheKey($version));
    }

    public function flushPageCache(Page $page): void
    {
        if ($page->currentPublishedVersion) {
            $this->flushVersionCache($page->currentPublishedVersion);
        }

        if ($page->currentDraftVersion) {
            $this->flushVersionCache($page->currentDraftVersion);
        }
    }

    public function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        return '/'.trim($path, '/');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRenderableBlocks(PageVersion $version): array
    {
        return $version->blocks
            ->filter(static fn ($block): bool => $block->is_enabled)
            ->map(function ($block): ?array {
                $definition = $block->blockDefinition;

                if (! $definition || ! $definition->is_active) {
                    return null;
                }

                $reusableBlock = $block->reusableBlock;
                $blockData = $reusableBlock?->data_json ?? $block->data_json ?? [];
                $blockLayout = array_replace(
                    $definition->default_layout_json ?? [],
                    $reusableBlock?->layout_json ?? [],
                    $block->layout_json ?? [],
                );
                $visibility = array_replace(
                    ['desktop' => true, 'tablet' => true, 'mobile' => true],
                    $reusableBlock?->visibility_json ?? [],
                    $block->visibility_json ?? [],
                );

                return [
                    'id' => $block->getKey(),
                    'internal_name' => $block->internal_name,
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
            ->filter(static fn (?array $item): bool => $item !== null)
            ->values()
            ->all();
    }

    private function cacheKey(PageVersion $version): string
    {
        $timestamp = $version->updated_at?->timestamp ?: 0;

        return "cms:render:page_version:{$version->getKey()}:{$timestamp}";
    }
}
