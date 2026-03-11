@php($stats = is_array($data['stats'] ?? null) ? $data['stats'] : [])
<div class="space-y-5">
    @if (!empty($data['title']))
        <h2 class="font-display text-2xl font-semibold text-current">{{ $data['title'] }}</h2>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-white/20 bg-white/10 p-5">
                <p class="font-display text-3xl font-semibold">
                    {{ $stat['prefix'] ?? '' }}{{ $stat['number'] ?? '0' }}{{ $stat['suffix'] ?? '' }}
                </p>
                <p class="mt-2 text-xs uppercase tracking-wide text-current/70">{{ $stat['label'] ?? '' }}</p>
                @if (!empty($stat['description']))
                    <p class="mt-2 text-sm text-current/75">{{ $stat['description'] }}</p>
                @endif
            </article>
        @endforeach
    </div>
</div>
