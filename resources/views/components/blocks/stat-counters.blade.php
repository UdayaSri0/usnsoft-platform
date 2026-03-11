@php($stats = is_array($data['stats'] ?? null) ? $data['stats'] : [])
<div class="space-y-8">
    @if (!empty($data['title']))
        <x-ui.public.section-heading :title="$data['title']" eyebrow="At a glance" inverted />
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                <p class="font-display text-3xl font-semibold">
                    {{ $stat['prefix'] ?? '' }}{{ $stat['number'] ?? '0' }}{{ $stat['suffix'] ?? '' }}
                </p>
                <p class="mt-2 text-xs uppercase tracking-[0.18em] text-current/70">{{ $stat['label'] ?? '' }}</p>
                @if (!empty($stat['description']))
                    <p class="mt-3 text-sm text-current/75">{{ $stat['description'] }}</p>
                @endif
            </article>
        @endforeach
    </div>
</div>
