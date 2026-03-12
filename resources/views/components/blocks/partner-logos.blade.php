@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-4">
    @if (!empty($data['title']))
        <x-ui.public.section-heading :title="$data['title']" eyebrow="Trusted by" />
    @endif

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @forelse ($items as $item)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-300">
                {{ $item['name'] ?? 'Partner' }}
            </div>
        @empty
            <x-ui.empty-state title="No partner logos yet" description="Seed or configure partner entries to display this trust strip." class="col-span-full" />
        @endforelse
    </div>
</div>
