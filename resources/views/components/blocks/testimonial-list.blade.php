@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Testimonials'" :intro="$data['intro'] ?? null" eyebrow="Social proof" />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($items as $item)
            <article class="usn-card h-full">
                <p class="text-sm leading-7 text-slate-700">“{{ $item['quote'] ?? '' }}”</p>
                <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $item['author'] ?? 'Client' }}</p>
                @if (!empty($item['role']))
                    <p class="mt-2 text-xs text-slate-500">{{ $item['role'] }}</p>
                @endif
            </article>
        @empty
            <x-ui.empty-state title="No testimonials configured yet" description="Add testimonial items to populate this proof section." class="md:col-span-2 xl:col-span-3" />
        @endforelse
    </div>
</div>
