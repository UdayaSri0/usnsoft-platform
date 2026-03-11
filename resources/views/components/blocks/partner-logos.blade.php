@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-4">
    @if (!empty($data['title']))
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] }}</h2>
    @endif

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @forelse ($items as $item)
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center text-xs font-semibold text-slate-500 shadow-sm">
                {{ $item['name'] ?? 'Partner' }}
            </div>
        @empty
            <p class="col-span-full rounded-xl border border-slate-200 bg-white/80 p-4 text-sm text-slate-600">No partner logos configured yet.</p>
        @endforelse
    </div>
</div>
