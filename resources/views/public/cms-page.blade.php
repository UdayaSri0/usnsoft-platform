<x-layouts.public :seo="$seo" :version="$version" :is-preview="$isPreview ?? false">
    @php($presentation = app(\App\Modules\Pages\Support\BlockPresentation::class))

    @if ($blocks === [])
        <section class="py-20">
            <div class="mx-auto max-w-4xl rounded-2xl border border-slate-300 bg-white p-8 text-center shadow-sm">
                <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ $version->title }}</h1>
                <p class="mt-4 text-slate-600">This page is published but currently has no visible content blocks.</p>
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
