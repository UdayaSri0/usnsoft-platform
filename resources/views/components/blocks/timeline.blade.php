@php
    $sourceMode = (string) ($data['source_mode'] ?? 'all');
    $limit = max(1, (int) ($data['item_limit'] ?? 8));
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];

    if ($sourceMode !== 'manual') {
        $items = app(\App\Modules\Showcase\Services\ShowcaseDirectoryService::class)
            ->timelineEntries($sourceMode === 'featured' ? 'featured' : 'all', $limit)
            ->map(function ($entry): array {
                return [
                    'date' => $entry->date_label ?: $entry->event_date?->format('Y'),
                    'title' => $entry->title,
                    'body' => $entry->summary ?: $entry->description,
                ];
            })
            ->all();
    }
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Timeline'" :intro="$data['intro'] ?? null" eyebrow="Journey" />

    <ol class="space-y-4">
        @forelse ($items as $item)
            <li class="relative rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/90">
                @if (!empty($item['date']))
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">{{ $item['date'] }}</p>
                @endif
                <h3 class="mt-2 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $item['title'] ?? 'Milestone' }}</h3>
                @if (!empty($item['body']))
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item['body']), 220) }}</p>
                @endif
            </li>
        @empty
            <x-ui.empty-state title="No timeline entries published yet" description="Publish timeline milestones to populate this company story block." />
        @endforelse
    </ol>
</div>
