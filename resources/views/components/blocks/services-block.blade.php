<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Services' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @for ($i = 0; $i < 3; $i++)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-slate-900">Service highlight {{ $i + 1 }}</h3>
                <p class="mt-2 text-sm text-slate-600">Service module integration will attach catalog records in the next stage.</p>
            </article>
        @endfor
    </div>

    @if (!empty($data['cta_label']) && !empty($data['cta_url']))
        <a href="{{ $data['cta_url'] }}" class="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">{{ $data['cta_label'] }}</a>
    @endif
</div>
