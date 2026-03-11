@php
    $draftModel = $draft ?? null;
    $existingBlocks = old('blocks');

    if (! is_array($existingBlocks)) {
        $existingBlocks = isset($draft)
            ? $draft->blocks->map(static fn ($block): array => [
                'block_type' => $block->blockDefinition?->key,
                'reusable_block_id' => $block->reusable_block_id,
                'region_key' => $block->region_key,
                'sort_order' => $block->sort_order,
                'internal_name' => $block->internal_name,
                'is_enabled' => $block->is_enabled,
                'data_json' => json_encode($block->data_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ])->values()->all()
            : [[
                'block_type' => 'hero',
                'reusable_block_id' => null,
                'region_key' => 'main',
                'sort_order' => 1,
                'internal_name' => 'Hero Section',
                'is_enabled' => true,
                'data_json' => json_encode(config('cms.definitions.hero.default_data', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ]];
    }

    $definitionOptions = $definitions->map(static fn ($definition): array => [
        'key' => $definition->key,
        'label' => $definition->name,
        'category' => $definition->category,
    ])->values()->all();

    $reusableOptions = $approvedReusableBlocks->map(static fn ($block): array => [
        'id' => $block->id,
        'label' => $block->name,
    ])->values()->all();

    $seoSnapshot = old('seo_snapshot_json');

    if (! is_array($seoSnapshot)) {
        $seoSnapshot = $draftModel?->seo_snapshot_json ?? [];
    }
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8" x-data="cmsEditor({
    blocks: @js($existingBlocks),
    definitions: @js($definitionOptions),
    reusable: @js($reusableOptions),
})">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-display text-lg font-semibold text-slate-900">Basics</h2>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @if (!empty($createMode))
                <div>
                    <x-input-label for="key" value="System key (optional)" />
                    <x-text-input id="key" name="key" class="mt-1 block w-full" :value="old('key')" />
                    <x-input-error :messages="$errors->get('key')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="page_type" value="Page type" />
                    <select id="page_type" name="page_type" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        @foreach (\App\Modules\Pages\Enums\PageType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('page_type', \App\Modules\Pages\Enums\PageType::Custom->value) === $type->value)>{{ ucfirst($type->value) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('page_type')" class="mt-2" />
                </div>

                <div class="md:col-span-2 flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_home" value="1" @checked((bool) old('is_home', false)) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        Home page
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_system_page" value="1" @checked((bool) old('is_system_page', false)) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        System page
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_locked_slug" value="1" @checked((bool) old('is_locked_slug', false)) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        Lock slug
                    </label>
                </div>
            @endif

            <div>
                <x-input-label for="title" value="Page title" />
                <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', data_get($draftModel, 'title'))" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-1 block w-full" :value="old('slug', data_get($draftModel, 'slug'))" required />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="path" value="Path" />
                <x-text-input id="path" name="path" class="mt-1 block w-full" :value="old('path', data_get($draftModel, 'path'))" />
                <p class="mt-1 text-xs text-slate-500">Leave blank to use the slug.</p>
                <x-input-error :messages="$errors->get('path')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="summary" value="Summary" />
                <textarea id="summary" name="summary" rows="3" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">{{ old('summary', data_get($draftModel, 'summary')) }}</textarea>
                <x-input-error :messages="$errors->get('summary')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="change_notes" value="Change notes" />
                <textarea id="change_notes" name="change_notes" rows="3" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">{{ old('change_notes', data_get($draftModel, 'change_notes')) }}</textarea>
                <x-input-error :messages="$errors->get('change_notes')" class="mt-2" />
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-display text-lg font-semibold text-slate-900">SEO Snapshot</h2>
        <p class="mt-1 text-sm text-slate-500">Saved per version for publish consistency.</p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="seo_snapshot_json_meta_title" value="Meta title" />
                <x-text-input id="seo_snapshot_json_meta_title" name="seo_snapshot_json[meta_title]" class="mt-1 block w-full" :value="$seoSnapshot['meta_title'] ?? ''" />
            </div>

            <div>
                <x-input-label for="seo_snapshot_json_canonical_url" value="Canonical URL" />
                <x-text-input id="seo_snapshot_json_canonical_url" name="seo_snapshot_json[canonical_url]" class="mt-1 block w-full" :value="$seoSnapshot['canonical_url'] ?? ''" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="seo_snapshot_json_meta_description" value="Meta description" />
                <textarea id="seo_snapshot_json_meta_description" name="seo_snapshot_json[meta_description]" rows="3" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500">{{ $seoSnapshot['meta_description'] ?? '' }}</textarea>
            </div>

            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="seo_snapshot_json[robots_index]" value="1" @checked((bool) ($seoSnapshot['robots_index'] ?? true)) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    Allow indexing
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="seo_snapshot_json[robots_follow]" value="1" @checked((bool) ($seoSnapshot['robots_follow'] ?? true)) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    Allow link follow
                </label>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-lg font-semibold text-slate-900">Blocks & Composition</h2>
                <p class="mt-1 text-sm text-slate-500">Safe schema-driven blocks. No raw executable markup is allowed.</p>
            </div>
            <button
                type="button"
                @click="addBlock()"
                class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400"
            >Add block</button>
        </div>

        <div class="mt-5 space-y-4">
            <template x-for="(block, index) in blocks" :key="index">
                <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-800" x-text="block.internal_name || `Block #${index + 1}`"></h3>
                        <button type="button" @click="removeBlock(index)" class="text-xs font-semibold uppercase tracking-wide text-rose-600">Remove</button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Definition</label>
                            <select :name="`blocks[${index}][block_type]`" x-model="block.block_type" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="">Select block</option>
                                <template x-for="definition in definitions" :key="definition.key">
                                    <option :value="definition.key" x-text="`${definition.label} (${definition.category})`"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Reusable block</label>
                            <select :name="`blocks[${index}][reusable_block_id]`" x-model="block.reusable_block_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <option value="">None</option>
                                <template x-for="reusableBlock in reusable" :key="reusableBlock.id">
                                    <option :value="reusableBlock.id" x-text="reusableBlock.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort order</label>
                            <input type="number" min="0" :name="`blocks[${index}][sort_order]`" x-model="block.sort_order" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Internal label</label>
                            <input type="text" :name="`blocks[${index}][internal_name]`" x-model="block.internal_name" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Region</label>
                            <input type="text" :name="`blocks[${index}][region_key]`" x-model="block.region_key" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="main">
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" value="1" :name="`blocks[${index}][is_enabled]`" x-model="block.is_enabled" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                Enabled
                            </label>
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Block data (JSON)</label>
                            <textarea :name="`blocks[${index}][data_json]`" x-model="block.data_json" rows="8" class="mt-1 block w-full rounded-xl border-slate-300 font-mono text-xs focus:border-sky-500 focus:ring-sky-500" placeholder="{\n  &quot;title&quot;: &quot;...&quot;\n}"></textarea>
                            <p class="mt-1 text-xs text-slate-500">Payload is sanitized and validated against the selected block schema before save.</p>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.cms.pages.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>

<script>
    function cmsEditor({ blocks, definitions, reusable }) {
        return {
            blocks: Array.isArray(blocks) && blocks.length > 0 ? blocks : [],
            definitions,
            reusable,
            addBlock() {
                this.blocks.push({
                    block_type: 'hero',
                    reusable_block_id: '',
                    region_key: 'main',
                    sort_order: this.blocks.length + 1,
                    internal_name: '',
                    is_enabled: true,
                    data_json: JSON.stringify({ title: '', body: '' }, null, 2),
                });
            },
            removeBlock(index) {
                this.blocks.splice(index, 1);
            },
        }
    }
</script>
