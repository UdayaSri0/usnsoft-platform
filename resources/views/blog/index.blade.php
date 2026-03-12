<x-layouts.public :seo="$seo">
    <section class="usn-section">
        <div class="usn-container-wide space-y-10">
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                <div>
                    <p class="usn-overline">Blog & News</p>
                    <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">Editorial updates, engineering notes, and company news.</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">Structured publishing with approval boundaries, searchable categories, and reusable content blocks.</p>
                </div>

                <form method="GET" class="usn-card grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="q" value="Search" />
                        <x-text-input id="q" name="q" class="mt-2 block w-full" :value="$filters['q']" placeholder="Search articles" />
                    </div>
                    <div>
                        <x-input-label for="category" value="Category" />
                        <x-select-input id="category" name="category" class="mt-2 block w-full">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div>
                        <x-input-label for="tag" value="Tag" />
                        <x-select-input id="tag" name="tag" class="mt-2 block w-full">
                            <option value="">All tags</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->slug }}" @selected($filters['tag'] === $tag->slug)>{{ $tag->name }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="sm:col-span-2 flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="featured" value="1" @checked($filters['featured']) class="usn-checkbox">
                            Featured only
                        </label>
                        <x-primary-button>Apply</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
                @forelse ($posts as $post)
                    <article class="usn-card flex h-full flex-col">
                        @if ($post->featuredImage && $post->featuredImage->disk === 'public')
                            <img src="{{ asset('storage/'.$post->featuredImage->path) }}" alt="{{ $post->title }}" class="h-48 w-full rounded-3xl object-cover">
                        @endif

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <span class="usn-badge-info">{{ $post->category?->name ?? 'News' }}</span>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $post->published_at?->format('M d, Y') }}</p>
                        </div>

                        <h2 class="mt-5 font-display text-2xl font-semibold text-slate-950">
                            <a href="{{ route('blog.show', ['post' => $post->slug]) }}" class="hover:text-sky-700">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">{{ $post->author?->name ?? 'USNsoft Editorial' }}</p>
                        @if ($post->excerpt)
                            <p class="mt-4 text-sm leading-6 text-slate-600">{{ $post->excerpt }}</p>
                        @endif

                        @if ($post->tags->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($post->tags as $tag)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">#{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-auto pt-6">
                            <a href="{{ route('blog.show', ['post' => $post->slug]) }}" class="usn-link">Read article</a>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No published posts yet" description="Check back for blog and newsroom updates." class="lg:col-span-2 xl:col-span-3" />
                @endforelse
            </div>

            <div>{{ $posts->links() }}</div>
        </div>
    </section>
</x-layouts.public>
