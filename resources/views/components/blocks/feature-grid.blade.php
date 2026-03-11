@php($items = is_array($data['items'] ?? null) ? $data['items'] : [])
<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? null" :intro="$data['intro'] ?? null" eyebrow="Highlights" />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($items as $index => $item)
            <article class="usn-card h-full">
                <div class="usn-icon-chip">{{ strtoupper(substr((string) ($item['icon'] ?? chr(65 + $index)), 0, 1)) }}</div>
                <h3 class="mt-5 font-display text-xl font-semibold text-slate-950">{{ $item['title'] ?? 'Feature' }}</h3>
                @if (!empty($item['body']))
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $item['body'] }}</p>
                @endif
                @if (!empty($item['link']))
                    <a href="{{ $item['link'] }}" class="mt-5 inline-flex usn-link">Learn more</a>
                @endif
            </article>
        @endforeach
    </div>
</div>
