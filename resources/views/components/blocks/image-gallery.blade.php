@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-5">
    @if (!empty($data['title']))
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] }}</h2>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            <figure class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="aspect-[4/3] bg-slate-100"></div>
                @if (!empty($item['caption']))
                    <figcaption class="px-4 py-3 text-xs text-slate-500">{{ $item['caption'] }}</figcaption>
                @endif
            </figure>
        @empty
            <p class="rounded-xl border border-slate-200 bg-white/80 p-4 text-sm text-slate-600">No gallery items configured yet.</p>
        @endforelse
    </div>
</div>
