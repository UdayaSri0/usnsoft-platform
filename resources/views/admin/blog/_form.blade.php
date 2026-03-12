@php
    $postModel = $post ?? null;
    $selectedTags = collect(old('tag_ids', $postModel?->tags->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
    $selectedRelated = collect(old('related_post_ids', $postModel?->relatedPosts->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
    $seo = old('seo');

    if (! is_array($seo)) {
        $seo = [
            'meta_title' => $postModel?->seoMeta?->meta_title,
            'meta_description' => $postModel?->seoMeta?->meta_description,
            'canonical_url' => $postModel?->seoMeta?->canonical_url,
            'og_title' => $postModel?->seoMeta?->og_title,
            'og_description' => $postModel?->seoMeta?->og_description,
            'og_image_media_id' => $postModel?->seoMeta?->og_image_media_id,
            'robots_index' => $postModel?->seoMeta?->robots_index ?? true,
            'robots_follow' => $postModel?->seoMeta?->robots_follow ?? true,
            'schema_type' => $postModel?->seoMeta?->schema_type,
        ];
    }

    $existingBlocks = old('blocks');
    if (! is_array($existingBlocks)) {
        $existingBlocks = collect($postModel?->content_blocks_json ?? [])
            ->map(static fn (array $block): array => [
                'block_type' => $block['block_type'] ?? 'rich_text',
                'reusable_block_id' => $block['reusable_block_id'] ?? null,
                'region_key' => $block['region_key'] ?? 'main',
                'sort_order' => $block['sort_order'] ?? 0,
                'internal_name' => $block['internal_name'] ?? null,
                'is_enabled' => $block['is_enabled'] ?? true,
                'data_json' => json_encode($block['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ])->values()->all();
    }

    if ($existingBlocks === []) {
        $existingBlocks = [[
            'block_type' => 'rich_text',
            'reusable_block_id' => null,
            'region_key' => 'main',
            'sort_order' => 1,
            'internal_name' => 'Body',
            'is_enabled' => true,
            'data_json' => json_encode(['content_html' => '<p>Start writing here.</p>'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
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
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8" x-data="cmsEditor({ blocks: @js($existingBlocks), definitions: @js($definitionOptions), reusable: @js($reusableOptions) })">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <x-ui.alert tone="danger" title="Validation errors">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">Basics</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-2 block w-full" :value="old('title', $postModel?->title)" required />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $postModel?->slug)" required />
            </div>

            <div>
                <x-input-label for="author_user_id" value="Author" />
                <x-select-input id="author_user_id" name="author_user_id" class="mt-2 block w-full">
                    <option value="">Select author</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->getKey() }}" @selected((string) old('author_user_id', $postModel?->author_user_id) === (string) $author->getKey())>{{ $author->name }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="blog_category_id" value="Category" />
                <x-select-input id="blog_category_id" name="blog_category_id" class="mt-2 block w-full">
                    <option value="">Uncategorized</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->getKey() }}" @selected((string) old('blog_category_id', $postModel?->blog_category_id) === (string) $category->getKey())>{{ $category->name }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="visibility" value="Visibility" />
                <x-select-input id="visibility" name="visibility" class="mt-2 block w-full">
                    @foreach (\App\Enums\VisibilityState::cases() as $visibility)
                        <option value="{{ $visibility->value }}" @selected(old('visibility', $postModel?->visibility?->value ?? \App\Enums\VisibilityState::Public->value) === $visibility->value)>{{ \Illuminate\Support\Str::headline($visibility->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="featured_flag" value="1" @checked((bool) old('featured_flag', $postModel?->featured_flag ?? false)) class="usn-checkbox">
                    Featured post
                </label>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="excerpt" value="Excerpt" />
                <x-textarea-input id="excerpt" name="excerpt" rows="4" class="mt-2 block w-full">{{ old('excerpt', $postModel?->excerpt) }}</x-textarea-input>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">Relations</h2>
        <div class="mt-4 grid gap-6 xl:grid-cols-3">
            <div>
                <x-input-label for="featured_image_media_id" value="Featured image" />
                <x-select-input id="featured_image_media_id" name="featured_image_media_id" class="mt-2 block w-full">
                    <option value="">None</option>
                    @foreach ($mediaAssets as $asset)
                        <option value="{{ $asset->getKey() }}" @selected((string) old('featured_image_media_id', $postModel?->featured_image_media_id) === (string) $asset->getKey())>{{ $asset->original_name }} [{{ $asset->disk }}]</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-900">Tags</p>
                <div class="mt-3 grid gap-2">
                    @foreach ($tags as $tag)
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->getKey() }}" @checked(in_array((string) $tag->getKey(), $selectedTags, true)) class="usn-checkbox">
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <x-input-label for="related_post_ids" value="Related posts" />
                <select id="related_post_ids" name="related_post_ids[]" multiple size="8" class="usn-select mt-2 block w-full">
                    @foreach ($relatedPosts as $relatedPost)
                        <option value="{{ $relatedPost->getKey() }}" @selected(in_array((string) $relatedPost->getKey(), $selectedRelated, true))>{{ $relatedPost->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-xl font-semibold text-slate-950">Structured Content Blocks</h2>
                <p class="mt-2 text-sm text-slate-500">Body content reuses the CMS block schema. Unsafe markup is sanitized before save.</p>
            </div>
            <button type="button" @click="addBlock()" class="usn-btn-secondary">Add block</button>
        </div>

        <div class="mt-5 space-y-4">
            <template x-for="(block, index) in blocks" :key="index">
                <article class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-800" x-text="block.internal_name || `Block #${index + 1}`"></h3>
                        <button type="button" @click="removeBlock(index)" class="text-xs font-semibold uppercase tracking-wide text-rose-600">Remove</button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Definition</label>
                            <select :name="`blocks[${index}][block_type]`" x-model="block.block_type" class="usn-select mt-2 block w-full">
                                <option value="">Select block</option>
                                <template x-for="definition in definitions" :key="definition.key">
                                    <option :value="definition.key" x-text="`${definition.label} (${definition.category})`"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Reusable block</label>
                            <select :name="`blocks[${index}][reusable_block_id]`" x-model="block.reusable_block_id" class="usn-select mt-2 block w-full">
                                <option value="">None</option>
                                <template x-for="reusableBlock in reusable" :key="reusableBlock.id">
                                    <option :value="reusableBlock.id" x-text="reusableBlock.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort order</label>
                            <input type="number" min="0" :name="`blocks[${index}][sort_order]`" x-model="block.sort_order" class="usn-input mt-2 block w-full">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Internal label</label>
                            <input type="text" :name="`blocks[${index}][internal_name]`" x-model="block.internal_name" class="usn-input mt-2 block w-full">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Region</label>
                            <input type="text" :name="`blocks[${index}][region_key]`" x-model="block.region_key" class="usn-input mt-2 block w-full" placeholder="main">
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" value="1" :name="`blocks[${index}][is_enabled]`" x-model="block.is_enabled" class="usn-checkbox">
                                Enabled
                            </label>
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Block data (JSON)</label>
                            <textarea :name="`blocks[${index}][data_json]`" x-model="block.data_json" rows="8" class="usn-textarea mt-2 block w-full font-mono text-xs"></textarea>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">SEO</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="seo_meta_title" value="Meta title" />
                <x-text-input id="seo_meta_title" name="seo[meta_title]" class="mt-2 block w-full" :value="$seo['meta_title'] ?? ''" />
            </div>
            <div>
                <x-input-label for="seo_canonical_url" value="Canonical URL" />
                <x-text-input id="seo_canonical_url" name="seo[canonical_url]" class="mt-2 block w-full" :value="$seo['canonical_url'] ?? ''" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="seo_meta_description" value="Meta description" />
                <x-textarea-input id="seo_meta_description" name="seo[meta_description]" rows="3" class="mt-2 block w-full">{{ $seo['meta_description'] ?? '' }}</x-textarea-input>
            </div>
            <div>
                <x-input-label for="seo_og_title" value="OG title" />
                <x-text-input id="seo_og_title" name="seo[og_title]" class="mt-2 block w-full" :value="$seo['og_title'] ?? ''" />
            </div>
            <div>
                <x-input-label for="seo_og_image_media_id" value="OG image" />
                <x-select-input id="seo_og_image_media_id" name="seo[og_image_media_id]" class="mt-2 block w-full">
                    <option value="">None</option>
                    @foreach ($mediaAssets as $asset)
                        <option value="{{ $asset->getKey() }}" @selected((string) ($seo['og_image_media_id'] ?? '') === (string) $asset->getKey())>{{ $asset->original_name }} [{{ $asset->disk }}]</option>
                    @endforeach
                </x-select-input>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="seo_og_description" value="OG description" />
                <x-textarea-input id="seo_og_description" name="seo[og_description]" rows="3" class="mt-2 block w-full">{{ $seo['og_description'] ?? '' }}</x-textarea-input>
            </div>
            <div>
                <x-input-label for="change_notes" value="Change notes" />
                <x-textarea-input id="change_notes" name="change_notes" rows="3" class="mt-2 block w-full">{{ old('change_notes', $postModel?->change_notes) }}</x-textarea-input>
            </div>
            <div class="flex items-end gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="seo[robots_index]" value="1" @checked((bool) ($seo['robots_index'] ?? true)) class="usn-checkbox">
                    Allow indexing
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="seo[robots_follow]" value="1" @checked((bool) ($seo['robots_follow'] ?? true)) class="usn-checkbox">
                    Allow follow
                </label>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.blog.index') }}" class="usn-btn-secondary">Cancel</a>
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
                    block_type: 'rich_text',
                    reusable_block_id: '',
                    region_key: 'main',
                    sort_order: this.blocks.length + 1,
                    internal_name: '',
                    is_enabled: true,
                    data_json: JSON.stringify({ content_html: '<p></p>' }, null, 2),
                });
            },
            removeBlock(index) {
                this.blocks.splice(index, 1);
            },
        }
    }
</script>
