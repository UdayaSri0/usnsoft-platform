<x-layouts.public :seo="$seo">
    <article class="usn-section">
        <div class="usn-container-wide">
            <a href="{{ route('blog.index') }}" class="usn-link">Back to Blog</a>

            <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_320px]">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="usn-badge-info">{{ $post->category?->name ?? 'News' }}</span>
                        <span class="text-sm text-slate-500">{{ $post->published_at?->format('M j, Y') }}</span>
                    </div>

                    <h1 class="mt-5 font-display text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $post->title }}</h1>
                    <p class="mt-4 text-sm font-medium text-slate-500">{{ $post->author?->name ?? 'USNsoft Editorial' }}</p>

                    @if ($post->excerpt)
                        <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-600">{{ $post->excerpt }}</p>
                    @endif

                    @if ($post->featuredImage && $post->featuredImage->disk === 'public')
                        <img src="{{ asset('storage/'.$post->featuredImage->path) }}" alt="{{ $post->title }}" class="mt-8 h-auto w-full rounded-[2rem] object-cover shadow-lg">
                    @endif

                    <div class="mt-10 space-y-8">
                        @forelse ($blocks as $block)
                            @php($layout = is_array($block['layout'] ?? null) ? $block['layout'] : [])
                            @php($visibility = is_array($block['visibility'] ?? null) ? $block['visibility'] : [])
                            <section class="{{ app(\App\Modules\Pages\Support\BlockPresentation::class)->wrapperClass($layout) }} {{ app(\App\Modules\Pages\Support\BlockPresentation::class)->visibilityClass($visibility) }}">
                                <div class="{{ app(\App\Modules\Pages\Support\BlockPresentation::class)->containerClass($layout) }}">
                                    @includeIf($block['view'], ['block' => $block, 'data' => $block['data'] ?? [], 'layout' => $layout, 'visibility' => $visibility, 'meta' => $block['meta'] ?? []])
                                </div>
                            </section>
                        @empty
                            <div class="usn-prose max-w-none">
                                <p>{{ $post->excerpt }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <aside class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Tags</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Related Posts</h2>
                        <div class="mt-4 space-y-4">
                            @forelse ($relatedPosts as $relatedPost)
                                <article>
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ $relatedPost->category?->name ?? 'News' }}</p>
                                    <a href="{{ route('blog.show', ['post' => $relatedPost->slug]) }}" class="mt-2 block font-display text-lg font-semibold text-slate-950 hover:text-sky-700">{{ $relatedPost->title }}</a>
                                    @if ($relatedPost->excerpt)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($relatedPost->excerpt, 110) }}</p>
                                    @endif
                                </article>
                            @empty
                                <p class="text-sm text-slate-500">No related posts yet.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </article>
</x-layouts.public>
