@php($limit = (int) ($data['item_limit'] ?? 3))
<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Blog' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @for ($i = 0; $i < max(1, $limit); $i++)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @if (($data['show_date'] ?? true) === true)
                    <p class="text-xs uppercase tracking-wide text-sky-700">{{ now()->subDays($i)->toFormattedDateString() }}</p>
                @endif
                <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Blog teaser {{ $i + 1 }}</h3>
                @if (($data['show_excerpt'] ?? true) === true)
                    <p class="mt-2 text-sm text-slate-600">Blog module integration will bind real content in the next stage.</p>
                @endif
                <a href="{{ url('/blog') }}" class="mt-3 inline-flex text-sm font-semibold text-sky-700">Read article</a>
            </article>
        @endfor
    </div>
</div>
