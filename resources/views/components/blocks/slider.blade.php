@php($slides = is_array($data['slides'] ?? null) ? $data['slides'] : [])
<div class="space-y-8">
    @if (!empty($data['title']))
        <x-ui.public.section-heading :title="$data['title']" eyebrow="Highlights" inverted />
    @endif

    @if ($slides === [])
        <x-ui.empty-state title="No slides configured yet" description="Add at least one slide to render this section." />
    @endif

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($slides as $slide)
            <article class="usn-card">
                <h3 class="font-display text-xl font-semibold text-slate-950">{{ $slide['title'] ?? 'Slide' }}</h3>
                @if (!empty($slide['subtitle']))
                    <p class="mt-2 text-sm font-medium text-sky-700">{{ $slide['subtitle'] }}</p>
                @endif
                @if (!empty($slide['body']))
                    <p class="mt-4 text-sm leading-6 text-slate-600">{{ $slide['body'] }}</p>
                @endif
                @if (!empty($slide['cta_label']) && !empty($slide['cta_url']))
                    <a href="{{ $slide['cta_url'] }}" class="mt-5 inline-flex usn-link">{{ $slide['cta_label'] }}</a>
                @endif
            </article>
        @endforeach
    </div>
</div>
