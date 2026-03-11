@php($slides = is_array($data['slides'] ?? null) ? $data['slides'] : [])
<div class="space-y-5">
    @if (!empty($data['title']))
        <h2 class="font-display text-2xl font-semibold tracking-tight">{{ $data['title'] }}</h2>
    @endif

    @if ($slides === [])
        <p class="rounded-xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-600">No slides configured yet.</p>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($slides as $slide)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-slate-900">{{ $slide['title'] ?? 'Slide' }}</h3>
                @if (!empty($slide['subtitle']))
                    <p class="mt-1 text-sm font-medium text-sky-700">{{ $slide['subtitle'] }}</p>
                @endif
                @if (!empty($slide['body']))
                    <p class="mt-3 text-sm text-slate-600">{{ $slide['body'] }}</p>
                @endif
                @if (!empty($slide['cta_label']) && !empty($slide['cta_url']))
                    <a href="{{ $slide['cta_url'] }}" class="mt-4 inline-flex text-sm font-semibold text-sky-700 hover:text-sky-800">{{ $slide['cta_label'] }}</a>
                @endif
            </article>
        @endforeach
    </div>
</div>
