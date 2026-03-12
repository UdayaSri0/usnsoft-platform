@php
    $sourceMode = (string) ($data['source_mode'] ?? 'all');
    $limit = max(1, (int) ($data['item_limit'] ?? 12));
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];

    if ($sourceMode !== 'manual') {
        $items = app(\App\Modules\Showcase\Services\ShowcaseDirectoryService::class)
            ->partners($sourceMode === 'featured', $limit)
            ->map(function ($partner): array {
                return [
                    'name' => $partner->name,
                    'url' => $partner->website_url,
                    'logo_url' => $partner->logo && $partner->logo->disk === 'public'
                        ? asset('storage/'.$partner->logo->path)
                        : null,
                ];
            })
            ->all();
    }
@endphp

<div class="space-y-4">
    @if (!empty($data['title']))
        <x-ui.public.section-heading :title="$data['title']" eyebrow="Trusted by" />
    @endif

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @forelse ($items as $item)
            @php
                $href = ($data['link_enabled'] ?? false) ? ($item['url'] ?? null) : null;
                $cardClasses = 'flex min-h-24 items-center justify-center rounded-2xl border border-slate-200 bg-white p-4 text-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-300';
                $imageClasses = 'max-h-12 w-auto max-w-full object-contain'.(($data['grayscale'] ?? true) ? ' grayscale opacity-75' : '');
            @endphp

            @if ($href)
                <a href="{{ $href }}" target="_blank" rel="noopener" class="{{ $cardClasses }}">
                    @if (!empty($item['logo_url']))
                        <img src="{{ $item['logo_url'] }}" alt="{{ $item['name'] ?? 'Partner' }}" class="{{ $imageClasses }}">
                    @else
                        {{ $item['name'] ?? 'Partner' }}
                    @endif
                </a>
            @else
                <div class="{{ $cardClasses }}">
                    @if (!empty($item['logo_url']))
                        <img src="{{ $item['logo_url'] }}" alt="{{ $item['name'] ?? 'Partner' }}" class="{{ $imageClasses }}">
                    @else
                        {{ $item['name'] ?? 'Partner' }}
                    @endif
                </div>
            @endif
        @empty
            <x-ui.empty-state title="No partner logos yet" description="Publish partner entries to display this trust strip." class="col-span-full" />
        @endforelse
    </div>
</div>
