@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Gallery'" eyebrow="Gallery" />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            <figure class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/90">
                <div class="aspect-[4/3] bg-[linear-gradient(135deg,_#dbeafe,_#f8fafc_52%,_#cffafe)] dark:bg-[linear-gradient(135deg,_rgba(14,116,144,0.55),_rgba(15,23,42,0.92)_58%,_rgba(8,145,178,0.45))]"></div>
                @if (!empty($item['caption']))
                    <figcaption class="px-4 py-3 text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">{{ $item['caption'] }}</figcaption>
                @endif
            </figure>
        @empty
            <x-ui.empty-state title="No gallery items yet" description="Add media items to turn this section into a visual gallery." class="sm:col-span-2 lg:col-span-3" />
        @endforelse
    </div>
</div>
