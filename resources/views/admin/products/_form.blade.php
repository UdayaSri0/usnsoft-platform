@php
    $draftModel = $draft ?? null;
    $seo = old('seo');

    if (! is_array($seo)) {
        $seo = [
            'meta_title' => $draftModel?->seoMeta?->meta_title,
            'meta_description' => $draftModel?->seoMeta?->meta_description,
            'canonical_url' => $draftModel?->seoMeta?->canonical_url,
            'og_title' => $draftModel?->seoMeta?->og_title,
            'og_description' => $draftModel?->seoMeta?->og_description,
            'og_image_media_id' => $draftModel?->seoMeta?->og_image_media_id,
            'robots_index' => $draftModel?->seoMeta?->robots_index ?? true,
            'robots_follow' => $draftModel?->seoMeta?->robots_follow ?? true,
            'schema_type' => $draftModel?->seoMeta?->schema_type,
        ];
    }

    $selectedTags = collect(old('tag_ids', $draftModel?->tags->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();

    $selectedPlatforms = collect(old('supported_platforms', $draftModel?->platforms->map(fn ($platform) => $platform->platform->value)->all() ?? []))
        ->map(fn ($platform) => (string) $platform)
        ->all();

    $selectedRelatedProducts = collect(old('related_product_ids', $draftModel?->relatedProducts->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();

    $faqItems = old('faq_items');
    if (! is_array($faqItems)) {
        $faqItems = $draftModel?->allFaqItems->map(static fn ($faq): array => [
            'question' => $faq->question,
            'answer' => $faq->answer,
            'is_visible' => $faq->is_visible,
        ])->values()->all() ?? [];
    }
    if ($faqItems === []) {
        $faqItems = [['question' => '', 'answer' => '', 'is_visible' => true]];
    }

    $screenshots = old('screenshots');
    if (! is_array($screenshots)) {
        $screenshots = $draftModel?->screenshots->map(static fn ($screenshot): array => [
            'media_asset_id' => $screenshot->media_asset_id,
            'caption' => $screenshot->caption,
        ])->values()->all() ?? [];
    }
    if ($screenshots === []) {
        $screenshots = [['media_asset_id' => '', 'caption' => '']];
    }

    $downloads = old('downloads');
    if (! is_array($downloads)) {
        $downloads = $draftModel?->downloads->map(static fn ($download): array => [
            'label' => $download->label,
            'description' => $download->description,
            'version_label' => $download->version_label,
            'download_mode' => $download->download_mode->value,
            'visibility' => $download->visibility->value,
            'external_url' => $download->external_url,
            'media_asset_id' => $download->media_asset_id,
            'is_primary' => $download->is_primary,
            'review_eligible' => $download->review_eligible,
            'notes' => $download->notes,
        ])->values()->all() ?? [];
    }
    if ($downloads === []) {
        $downloads = [[
            'label' => 'Primary download',
            'description' => '',
            'version_label' => '',
            'download_mode' => \App\Modules\Products\Enums\ProductDownloadMode::ManualRequest->value,
            'visibility' => \App\Modules\Products\Enums\ProductDownloadVisibility::Authenticated->value,
            'external_url' => '',
            'media_asset_id' => '',
            'is_primary' => true,
            'review_eligible' => true,
            'notes' => '',
        ]];
    }

    $mediaOptions = $mediaAssets->map(static fn ($asset): array => [
        'id' => $asset->getKey(),
        'label' => $asset->original_name.' ['.$asset->disk.']',
    ])->values()->all();

    $downloadModeOptions = collect($downloadModes)->map(static fn ($mode): array => [
        'value' => $mode->value,
        'label' => \Illuminate\Support\Str::headline($mode->value),
    ])->values()->all();

    $downloadVisibilityOptions = collect($downloadVisibilities)->map(static fn ($visibility): array => [
        'value' => $visibility->value,
        'label' => \Illuminate\Support\Str::headline($visibility->value),
    ])->values()->all();
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-8"
    x-data="productEditor({
        faqs: @js($faqItems),
        screenshots: @js($screenshots),
        downloads: @js($downloads),
        mediaOptions: @js($mediaOptions),
        downloadModes: @js($downloadModeOptions),
        downloadVisibilities: @js($downloadVisibilityOptions),
    })"
>
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
                <x-input-label for="name" value="Product name" />
                <x-text-input id="name" name="name" class="mt-2 block w-full" :value="old('name', $draftModel?->name ?? null)" required />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $draftModel?->slug ?? null)" required />
            </div>

            <div>
                <x-input-label for="product_category_id" value="Category" />
                <x-select-input id="product_category_id" name="product_category_id" class="mt-2 block w-full">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->getKey() }}" @selected((string) old('product_category_id', $draftModel?->product_category_id) === (string) $category->getKey())>{{ $category->name }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="product_kind" value="Product kind" />
                <x-select-input id="product_kind" name="product_kind" class="mt-2 block w-full">
                    @foreach ($productKinds as $kind)
                        <option value="{{ $kind->value }}" @selected(old('product_kind', $draftModel?->product_kind?->value ?? \App\Modules\Products\Enums\ProductKind::WebApp->value) === $kind->value)>{{ \Illuminate\Support\Str::headline($kind->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="current_version" value="Current version" />
                <x-text-input id="current_version" name="current_version" class="mt-2 block w-full" :value="old('current_version', $draftModel?->current_version)" />
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="featured_flag" value="1" @checked((bool) old('featured_flag', $draftModel?->featured_flag ?? false)) class="usn-checkbox">
                    Featured product
                </label>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="short_description" value="Short description" />
                <x-textarea-input id="short_description" name="short_description" rows="3" class="mt-2 block w-full">{{ old('short_description', $draftModel?->short_description) }}</x-textarea-input>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="full_description" value="Full description" />
                <x-textarea-input id="full_description" name="full_description" rows="5" class="mt-2 block w-full">{{ old('full_description', $draftModel?->full_description) }}</x-textarea-input>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="rich_body" value="Rich body" />
                <x-textarea-input id="rich_body" name="rich_body" rows="10" class="mt-2 block w-full">{{ old('rich_body', $draftModel?->rich_body) }}</x-textarea-input>
                <p class="mt-2 text-xs text-slate-500">Sanitized rich content only. Scripts and unsafe inline handlers are stripped.</p>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">Classification and relations</h2>
        <div class="mt-4 grid gap-6 xl:grid-cols-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">Supported platforms</p>
                <div class="mt-3 grid gap-2">
                    @foreach ($platforms as $platform)
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="supported_platforms[]" value="{{ $platform->value }}" @checked(in_array($platform->value, $selectedPlatforms, true)) class="usn-checkbox">
                            {{ \Illuminate\Support\Str::headline($platform->value) }}
                        </label>
                    @endforeach
                </div>
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
                <x-input-label for="related_product_ids" value="Related products" />
                <select id="related_product_ids" name="related_product_ids[]" multiple size="8" class="usn-select mt-2 block w-full">
                    @foreach ($relatedProducts as $relatedProduct)
                        <option value="{{ $relatedProduct->getKey() }}" @selected(in_array((string) $relatedProduct->getKey(), $selectedRelatedProducts, true))>{{ $relatedProduct->name_current }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Use Ctrl/Cmd click to select multiple products.</p>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">Media and support</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-input-label for="featured_image_media_id" value="Featured image asset" />
                <x-select-input id="featured_image_media_id" name="featured_image_media_id" class="mt-2 block w-full">
                    <option value="">None</option>
                    @foreach ($mediaAssets as $asset)
                        <option value="{{ $asset->getKey() }}" @selected((string) old('featured_image_media_id', $draftModel?->featured_image_media_id) === (string) $asset->getKey())>{{ $asset->original_name }} [{{ $asset->disk }}]</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="video_url" value="Video URL" />
                <x-text-input id="video_url" name="video_url" class="mt-2 block w-full" :value="old('video_url', $draftModel?->video_url)" />
            </div>

            <div>
                <x-input-label for="support_contact" value="Support contact" />
                <x-text-input id="support_contact" name="support_contact" class="mt-2 block w-full" :value="old('support_contact', $draftModel?->support_contact)" />
            </div>
        </div>

        <div class="mt-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-display text-lg font-semibold text-slate-950">Screenshots gallery</h3>
                    <p class="mt-2 text-sm text-slate-500">Attach public media assets for the Screenshots tab.</p>
                </div>
                <button type="button" @click="addScreenshot()" class="usn-btn-secondary">Add screenshot</button>
            </div>

            <div class="mt-5 space-y-4">
                <template x-for="(screenshot, index) in screenshots" :key="`screenshot-${index}`">
                    <article class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="grid gap-3 md:grid-cols-[1.2fr_1fr_auto]">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Media asset</label>
                                <select :name="`screenshots[${index}][media_asset_id]`" x-model="screenshot.media_asset_id" class="usn-select mt-2 block w-full">
                                    <option value="">Select media</option>
                                    <template x-for="asset in mediaOptions" :key="asset.id">
                                        <option :value="asset.id" x-text="asset.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Caption</label>
                                <input type="text" :name="`screenshots[${index}][caption]`" x-model="screenshot.caption" class="usn-input mt-2 block w-full">
                            </div>
                            <div class="flex items-end">
                                <button type="button" @click="removeScreenshot(index)" class="usn-btn-secondary">Remove</button>
                            </div>
                        </div>
                    </article>
                </template>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">Release and documentation</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-input-label for="pricing_mode" value="Pricing mode" />
                <x-select-input id="pricing_mode" name="pricing_mode" class="mt-2 block w-full">
                    @foreach ($pricingModes as $mode)
                        <option value="{{ $mode->value }}" @selected(old('pricing_mode', $draftModel?->pricing_mode?->value ?? \App\Modules\Products\Enums\ProductPricingMode::Free->value) === $mode->value)>{{ \Illuminate\Support\Str::headline($mode->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="pricing_text" value="Pricing text" />
                <x-text-input id="pricing_text" name="pricing_text" class="mt-2 block w-full" :value="old('pricing_text', $draftModel?->pricing_text)" />
            </div>

            <div>
                <x-input-label for="documentation_link" value="Documentation link" />
                <x-text-input id="documentation_link" name="documentation_link" class="mt-2 block w-full" :value="old('documentation_link', $draftModel?->documentation_link)" />
            </div>

            <div>
                <x-input-label for="github_link" value="GitHub link" />
                <x-text-input id="github_link" name="github_link" class="mt-2 block w-full" :value="old('github_link', $draftModel?->github_link)" />
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="release_notes" value="Release notes" />
                <x-textarea-input id="release_notes" name="release_notes" rows="6" class="mt-2 block w-full">{{ old('release_notes', $draftModel?->release_notes) }}</x-textarea-input>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="changelog" value="Changelog" />
                <x-textarea-input id="changelog" name="changelog" rows="6" class="mt-2 block w-full">{{ old('changelog', $draftModel?->changelog) }}</x-textarea-input>
            </div>

            <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="release_notes_visible" value="1" @checked((bool) old('release_notes_visible', $draftModel?->release_notes_visible ?? true)) class="usn-checkbox">
                    Show release notes tab content
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="changelog_visible" value="1" @checked((bool) old('changelog_visible', $draftModel?->changelog_visible ?? true)) class="usn-checkbox">
                    Show changelog tab content
                </label>
            </div>
        </div>
    </section>

    <section class="usn-card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-xl font-semibold text-slate-950">Downloads</h2>
                <p class="mt-2 text-sm text-slate-500">Configure download mode, visibility, version awareness, and review eligibility per entry.</p>
            </div>
            <button type="button" @click="addDownload()" class="usn-btn-secondary">Add download</button>
        </div>

        <div class="mt-5 space-y-4">
            <template x-for="(download, index) in downloads" :key="`download-${index}`">
                <article class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="grid gap-3 xl:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Label</label>
                            <input type="text" :name="`downloads[${index}][label]`" x-model="download.label" class="usn-input mt-2 block w-full">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Version label</label>
                            <input type="text" :name="`downloads[${index}][version_label]`" x-model="download.version_label" class="usn-input mt-2 block w-full">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Download mode</label>
                            <select :name="`downloads[${index}][download_mode]`" x-model="download.download_mode" class="usn-select mt-2 block w-full">
                                <template x-for="mode in downloadModes" :key="mode.value">
                                    <option :value="mode.value" x-text="mode.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Visibility</label>
                            <select :name="`downloads[${index}][visibility]`" x-model="download.visibility" class="usn-select mt-2 block w-full">
                                <template x-for="visibility in downloadVisibilities" :key="visibility.value">
                                    <option :value="visibility.value" x-text="visibility.label"></option>
                                </template>
                            </select>
                        </div>

                        <div class="xl:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Description</label>
                            <textarea :name="`downloads[${index}][description]`" x-model="download.description" rows="3" class="usn-textarea mt-2 block w-full"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">External URL</label>
                            <input type="url" :name="`downloads[${index}][external_url]`" x-model="download.external_url" class="usn-input mt-2 block w-full">
                            <p class="mt-1 text-xs text-slate-500">Required for external, GitHub, App Store, and Play Store modes.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Media asset</label>
                            <select :name="`downloads[${index}][media_asset_id]`" x-model="download.media_asset_id" class="usn-select mt-2 block w-full">
                                <option value="">Select media</option>
                                <template x-for="asset in mediaOptions" :key="asset.id">
                                    <option :value="asset.id" x-text="asset.label"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Required for direct and protected private download modes.</p>
                        </div>

                        <div class="xl:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</label>
                            <input type="text" :name="`downloads[${index}][notes]`" x-model="download.notes" class="usn-input mt-2 block w-full">
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" value="1" :name="`downloads[${index}][is_primary]`" x-model="download.is_primary" class="usn-checkbox">
                                Primary CTA
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" value="1" :name="`downloads[${index}][review_eligible]`" x-model="download.review_eligible" class="usn-checkbox">
                                Enables review verification
                            </label>
                        </div>

                        <div class="flex items-end justify-end">
                            <button type="button" @click="removeDownload(index)" class="usn-btn-secondary">Remove</button>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <section class="usn-card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-xl font-semibold text-slate-950">FAQ</h2>
                <p class="mt-2 text-sm text-slate-500">Each visible FAQ item becomes a conditional tab section on the public product page.</p>
            </div>
            <button type="button" @click="addFaq()" class="usn-btn-secondary">Add FAQ</button>
        </div>

        <div class="mt-5 space-y-4">
            <template x-for="(faq, index) in faqs" :key="`faq-${index}`">
                <article class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="grid gap-3 xl:grid-cols-[1fr_1.3fr_auto]">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Question</label>
                            <input type="text" :name="`faq_items[${index}][question]`" x-model="faq.question" class="usn-input mt-2 block w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Answer</label>
                            <textarea :name="`faq_items[${index}][answer]`" x-model="faq.answer" rows="4" class="usn-textarea mt-2 block w-full"></textarea>
                        </div>
                        <div class="flex flex-col justify-end gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" value="1" :name="`faq_items[${index}][is_visible]`" x-model="faq.is_visible" class="usn-checkbox">
                                Visible
                            </label>
                            <button type="button" @click="removeFaq(index)" class="usn-btn-secondary">Remove</button>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </section>

    <section class="usn-card">
        <h2 class="font-display text-xl font-semibold text-slate-950">Visibility, reviews, and SEO</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-input-label for="product_visibility" value="Product visibility" />
                <x-select-input id="product_visibility" name="product_visibility" class="mt-2 block w-full">
                    @foreach ($productVisibilities as $visibility)
                        <option value="{{ $visibility->value }}" @selected(old('product_visibility', $draftModel?->product_visibility?->value ?? \App\Modules\Products\Enums\ProductVisibility::Public->value) === $visibility->value)>{{ \Illuminate\Support\Str::headline($visibility->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div>
                <x-input-label for="download_visibility" value="Default download visibility" />
                <x-select-input id="download_visibility" name="download_visibility" class="mt-2 block w-full">
                    @foreach ($downloadVisibilities as $visibility)
                        <option value="{{ $visibility->value }}" @selected(old('download_visibility', $draftModel?->download_visibility?->value ?? \App\Modules\Products\Enums\ProductDownloadVisibility::Verified->value) === $visibility->value)>{{ \Illuminate\Support\Str::headline($visibility->value) }}</option>
                    @endforeach
                </x-select-input>
            </div>

            <div class="md:col-span-2 xl:col-span-1">
                <x-input-label for="change_notes" value="Change notes" />
                <x-textarea-input id="change_notes" name="change_notes" rows="3" class="mt-2 block w-full">{{ old('change_notes', $draftModel?->change_notes) }}</x-textarea-input>
            </div>

            <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="reviews_enabled" value="1" @checked((bool) old('reviews_enabled', $draftModel?->reviews_enabled ?? true)) class="usn-checkbox">
                    Enable public reviews
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="review_requires_verification" value="1" @checked((bool) old('review_requires_verification', $draftModel?->review_requires_verification ?? true)) class="usn-checkbox">
                    Require verification before review
                </label>
            </div>

            <div>
                <x-input-label for="seo_meta_title" value="Meta title" />
                <x-text-input id="seo_meta_title" name="seo[meta_title]" class="mt-2 block w-full" :value="$seo['meta_title'] ?? ''" />
            </div>

            <div>
                <x-input-label for="seo_canonical_url" value="Canonical URL" />
                <x-text-input id="seo_canonical_url" name="seo[canonical_url]" class="mt-2 block w-full" :value="$seo['canonical_url'] ?? ''" />
            </div>

            <div>
                <x-input-label for="seo_schema_type" value="Schema type" />
                <x-text-input id="seo_schema_type" name="seo[schema_type]" class="mt-2 block w-full" :value="$seo['schema_type'] ?? ''" />
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <x-input-label for="seo_meta_description" value="Meta description" />
                <x-textarea-input id="seo_meta_description" name="seo[meta_description]" rows="3" class="mt-2 block w-full">{{ $seo['meta_description'] ?? '' }}</x-textarea-input>
            </div>

            <div>
                <x-input-label for="seo_og_title" value="OG title" />
                <x-text-input id="seo_og_title" name="seo[og_title]" class="mt-2 block w-full" :value="$seo['og_title'] ?? ''" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="seo_og_description" value="OG description" />
                <x-textarea-input id="seo_og_description" name="seo[og_description]" rows="3" class="mt-2 block w-full">{{ $seo['og_description'] ?? '' }}</x-textarea-input>
            </div>

            <div>
                <x-input-label for="seo_og_image_media_id" value="OG image asset" />
                <x-select-input id="seo_og_image_media_id" name="seo[og_image_media_id]" class="mt-2 block w-full">
                    <option value="">None</option>
                    @foreach ($mediaAssets as $asset)
                        <option value="{{ $asset->getKey() }}" @selected((string) ($seo['og_image_media_id'] ?? '') === (string) $asset->getKey())>{{ $asset->original_name }} [{{ $asset->disk }}]</option>
                    @endforeach
                </x-select-input>
            </div>

            <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="seo[robots_index]" value="1" @checked((bool) ($seo['robots_index'] ?? true)) class="usn-checkbox">
                    Allow indexing
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="seo[robots_follow]" value="1" @checked((bool) ($seo['robots_follow'] ?? true)) class="usn-checkbox">
                    Allow link follow
                </label>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.products.index') }}" class="usn-btn-secondary">Cancel</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>

<script>
    function productEditor({ faqs, screenshots, downloads, mediaOptions, downloadModes, downloadVisibilities }) {
        return {
            faqs: Array.isArray(faqs) ? faqs : [],
            screenshots: Array.isArray(screenshots) ? screenshots : [],
            downloads: Array.isArray(downloads) ? downloads : [],
            mediaOptions,
            downloadModes,
            downloadVisibilities,
            addFaq() {
                this.faqs.push({ question: '', answer: '', is_visible: true });
            },
            removeFaq(index) {
                this.faqs.splice(index, 1);
            },
            addScreenshot() {
                this.screenshots.push({ media_asset_id: '', caption: '' });
            },
            removeScreenshot(index) {
                this.screenshots.splice(index, 1);
            },
            addDownload() {
                this.downloads.push({
                    label: 'Download',
                    description: '',
                    version_label: '',
                    download_mode: 'manual_request',
                    visibility: 'authenticated',
                    external_url: '',
                    media_asset_id: '',
                    is_primary: false,
                    review_eligible: true,
                    notes: '',
                });
            },
            removeDownload(index) {
                this.downloads.splice(index, 1);
            },
        }
    }
</script>
