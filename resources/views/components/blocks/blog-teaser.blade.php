@php
    $catalog = app(\App\Modules\Blog\Services\BlogCatalogService::class);
    $sourceMode = (string) ($data['source_mode'] ?? 'latest');
    $limit = max(1, (int) ($data['item_limit'] ?? 3));

    $posts = match ($sourceMode) {
        'featured' => $catalog->featuredPublished($limit),
        'category' => $catalog->latestPublished($limit, (string) ($data['category_slug'] ?? '')),
        'tags' => $catalog->latestPublished($limit, null, (string) ($data['tag_slug'] ?? '')),
        'manual' => $catalog->manualSelection(
            collect($data['post_slugs'] ?? [])->filter(fn ($slug) => is_string($slug))->values()->all(),
            $limit,
        ),
        default => $catalog->latestPublished($limit),
    };
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading
        eyebrow="Insights"
        :title="$data['title'] ?? 'Blog & News'"
        :intro="$data['intro'] ?? 'Recent thinking from the USNsoft platform, security, and delivery teams.'"
    />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($posts as $post)
            <article class="usn-card flex h-full flex-col">
                @if (($data['show_image'] ?? true) === true && $post->featuredImage && $post->featuredImage->disk === 'public')
                    <img src="{{ asset('storage/'.$post->featuredImage->path) }}" alt="{{ $post->title }}" class="h-44 w-full rounded-3xl object-cover">
                @endif

                <div class="mt-5 flex items-center justify-between gap-3">
                    <span class="usn-badge-info">{{ $post->category?->name ?? 'News' }}</span>
                    @if (($data['show_date'] ?? true) === true)
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $post->published_at?->format('M d, Y') }}</p>
                    @endif
                </div>

                <h3 class="mt-5 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $post->title }}</h3>

                @if (($data['show_author'] ?? true) === true)
                    <p class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $post->author?->name ?? 'USNsoft Editorial' }}</p>
                @endif

                @if (($data['show_excerpt'] ?? true) === true && $post->excerpt)
                    <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $post->excerpt }}</p>
                @endif

                <div class="mt-auto pt-6">
                    <a href="{{ route('blog.show', ['post' => $post->slug]) }}" class="usn-link">Read update</a>
                </div>
            </article>
        @empty
            <x-ui.empty-state title="No published posts yet" description="Publish blog posts to populate this editorial teaser block." class="md:col-span-2 xl:col-span-3" />
        @endforelse
    </div>
</div>
