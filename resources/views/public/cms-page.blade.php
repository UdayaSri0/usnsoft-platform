<x-layouts.public :seo="$seo" :version="$version" :is-preview="$isPreview ?? false">
    @php($presentation = app(\App\Modules\Pages\Support\BlockPresentation::class))

    @if ($blocks === [])
        <section class="usn-section">
            <div class="usn-container-narrow">
                <x-ui.empty-state
                    :title="$version->title"
                    description="This page is published, but it currently has no visible content blocks."
                />
            </div>
        </section>
    @endif

    @foreach ($blocks as $block)
        @php($layout = is_array($block['layout'] ?? null) ? $block['layout'] : [])
        @php($visibility = is_array($block['visibility'] ?? null) ? $block['visibility'] : [])

        <section class="{{ $presentation->wrapperClass($layout) }} {{ $presentation->visibilityClass($visibility) }}">
            <div class="{{ $presentation->containerClass($layout) }}">
                @includeIf($block['view'], [
                    'block' => $block,
                    'data' => $block['data'] ?? [],
                    'layout' => $layout,
                    'meta' => $block['meta'] ?? [],
                ])

                @unless (view()->exists($block['view']))
                    @include('components.blocks.fallback', [
                        'block' => $block,
                    ])
                @endunless
            </div>
        </section>
    @endforeach
</x-layouts.public>
