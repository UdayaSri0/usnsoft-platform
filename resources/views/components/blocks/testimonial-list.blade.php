@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Testimonials' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($items as $item)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-700">“{{ $item['quote'] ?? '' }}”</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $item['author'] ?? 'Client' }}</p>
                @if (!empty($item['role']))
                    <p class="text-xs text-slate-500">{{ $item['role'] }}</p>
                @endif
            </article>
        @empty
            <p class="rounded-xl border border-slate-200 bg-white/80 p-4 text-sm text-slate-600">No testimonials configured yet.</p>
        @endforelse
    </div>
</div>
