@php
    $sourceMode = (string) ($data['source_mode'] ?? 'all');
    $limit = max(1, (int) ($data['item_limit'] ?? 8));
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];

    if ($sourceMode !== 'manual') {
        $items = app(\App\Modules\Showcase\Services\ShowcaseDirectoryService::class)
            ->teamMembers($sourceMode === 'featured' ? 'featured' : 'all', $limit)
            ->map(function ($member): array {
                return [
                    'name' => $member->full_name,
                    'role' => $member->role_title,
                    'bio' => $member->short_bio,
                    'image_url' => $member->photo && $member->photo->disk === 'public'
                        ? asset('storage/'.$member->photo->path)
                        : null,
                    'profile_url' => $member->website_url ?: $member->linkedin_url,
                ];
            })
            ->all();
    }
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading :title="$data['title'] ?? 'Team'" :intro="$data['intro'] ?? null" eyebrow="Leadership" />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        @forelse ($items as $item)
            <article class="usn-card h-full">
                @if (!empty($item['image_url']))
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] ?? 'Team Member' }}" class="mb-5 h-20 w-20 rounded-full object-cover">
                @else
                    <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-[linear-gradient(135deg,_#0f5f92,_#dbeafe)] text-lg font-semibold text-white">
                        {{ strtoupper(\Illuminate\Support\Str::substr((string) ($item['name'] ?? 'TM'), 0, 2)) }}
                    </div>
                @endif

                <h3 class="font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $item['name'] ?? 'Team Member' }}</h3>
                @if (!empty($item['role']))
                    <p class="mt-2 text-xs uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">{{ $item['role'] }}</p>
                @endif
                @if (!empty($item['bio']))
                    <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $item['bio'] }}</p>
                @endif
                @if (!empty($item['profile_url']))
                    <a href="{{ $item['profile_url'] }}" target="_blank" rel="noopener" class="mt-5 inline-flex usn-link">View profile</a>
                @endif
            </article>
        @empty
            <x-ui.empty-state title="No team members published yet" description="Publish team entries to populate this section." class="md:col-span-2 xl:col-span-4" />
        @endforelse
    </div>
</div>
