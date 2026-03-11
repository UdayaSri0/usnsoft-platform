@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-6">
    @if (!empty($data['title']))
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] }}</h2>
    @endif
    @if (!empty($data['intro']))
        <p class="max-w-3xl text-sm text-slate-600">{{ $data['intro'] }}</p>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($items as $item)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-slate-900">{{ $item['title'] ?? 'Feature' }}</h3>
                @if (!empty($item['body']))
                    <p class="mt-2 text-sm text-slate-600">{{ $item['body'] }}</p>
                @endif
                @if (!empty($item['link']))
                    <a href="{{ $item['link'] }}" class="mt-4 inline-flex text-sm font-semibold text-sky-700">Learn more</a>
                @endif
            </article>
        @endforeach
    </div>
</div>
