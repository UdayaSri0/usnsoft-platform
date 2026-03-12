@php
    $sourceMode = (string) ($data['source_mode'] ?? 'manual');
    $limit = max(1, (int) ($data['item_limit'] ?? 4));
    $stats = is_array($data['stats'] ?? null) ? $data['stats'] : [];

    if ($sourceMode !== 'manual') {
        $stats = app(\App\Modules\Showcase\Services\ShowcaseDirectoryService::class)
            ->achievements($sourceMode === 'featured_achievements' ? 'featured' : 'all', $limit)
            ->filter(fn ($achievement) => $achievement->metric_value !== null && $achievement->metric_value !== '')
            ->map(function ($achievement): array {
                return [
                    'number' => $achievement->metric_value,
                    'label' => $achievement->title,
                    'prefix' => $achievement->metric_prefix,
                    'suffix' => $achievement->metric_suffix,
                    'description' => $achievement->summary,
                ];
            })
            ->values()
            ->all();
    }
@endphp

<div class="space-y-8">
    @if (!empty($data['title']))
        <x-ui.public.section-heading :title="$data['title']" eyebrow="At a glance" inverted />
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($stats as $stat)
            <article class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                <p class="font-display text-3xl font-semibold">
                    {{ $stat['prefix'] ?? '' }}{{ $stat['number'] ?? '0' }}{{ $stat['suffix'] ?? '' }}
                </p>
                <p class="mt-2 text-xs uppercase tracking-[0.18em] text-current/70">{{ $stat['label'] ?? '' }}</p>
                @if (!empty($stat['description']))
                    <p class="mt-3 text-sm text-current/75">{{ $stat['description'] }}</p>
                @endif
            </article>
        @empty
            <x-ui.empty-state title="No achievement counters available" description="Publish achievements with metrics to populate this highlight block." class="sm:col-span-2 lg:col-span-4" />
        @endforelse
    </div>
</div>
