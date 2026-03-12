@php
    $sourceMode = (string) ($data['source_mode'] ?? 'recent');
    $limit = max(1, (int) ($data['item_limit'] ?? 6));
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];

    if ($sourceMode !== 'manual') {
        $items = app(\App\Modules\Showcase\Services\ShowcaseDirectoryService::class)
            ->testimonials($sourceMode === 'featured' ? 'featured' : 'recent', $limit)
            ->map(function ($testimonial): array {
                $roleParts = array_filter([$testimonial->role_title, $testimonial->company_name]);

                return [
                    'quote' => $testimonial->quote,
                    'author' => $testimonial->client_name,
                    'role' => implode(' · ', $roleParts),
                    'avatar_url' => $testimonial->avatar && $testimonial->avatar->disk === 'public'
                        ? asset('storage/'.$testimonial->avatar->path)
                        : null,
                    'rating' => $testimonial->rating,
                ];
            })
            ->all();
    }
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Testimonials'" :intro="$data['intro'] ?? null" eyebrow="Social proof" />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($items as $item)
            <article class="usn-card h-full">
                <div class="flex items-center gap-3">
                    @if (!empty($item['avatar_url']))
                        <img src="{{ $item['avatar_url'] }}" alt="{{ $item['author'] ?? 'Client' }}" class="h-12 w-12 rounded-full object-cover">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,_#0f5f92,_#dbeafe)] text-xs font-semibold text-white">
                            {{ strtoupper(\Illuminate\Support\Str::substr((string) ($item['author'] ?? 'C'), 0, 2)) }}
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item['author'] ?? 'Client' }}</p>
                        @if (!empty($item['role']))
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['role'] }}</p>
                        @endif
                    </div>
                </div>

                <p class="mt-5 text-sm leading-7 text-slate-700 dark:text-slate-300">“{{ $item['quote'] ?? '' }}”</p>

                @if (!empty($item['rating']))
                    <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-300">{{ str_repeat('★', (int) $item['rating']) }}</p>
                @endif
            </article>
        @empty
            <x-ui.empty-state title="No testimonials configured yet" description="Publish testimonials to populate this proof section." class="md:col-span-2 xl:col-span-3" />
        @endforelse
    </div>
</div>
