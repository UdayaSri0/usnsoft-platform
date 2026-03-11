@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Gallery'" eyebrow="Gallery" />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            <figure class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="aspect-[4/3] bg-[linear-gradient(135deg,_#dbeafe,_#f8fafc_52%,_#cffafe)]"></div>
                @if (!empty($item['caption']))
                    <figcaption class="px-4 py-3 text-xs font-medium uppercase tracking-[0.16em] text-slate-500">{{ $item['caption'] }}</figcaption>
                @endif
            </figure>
        @empty
            <x-ui.empty-state title="No gallery items yet" description="Add media items to turn this section into a visual gallery." class="sm:col-span-2 lg:col-span-3" />
        @endforelse
    </div>
</div>
