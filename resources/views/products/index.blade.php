<x-layouts.public :seo="$seo">
    <section class="usn-surface-brand usn-section-xl">
        <div class="usn-container-wide">
            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Product Platform</span>
                    <h1 class="mt-5 font-display text-4xl font-semibold leading-tight text-white sm:text-5xl">Controlled product publishing, release visibility, and protected download delivery.</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-200">Discover desktop, mobile, web, plugin, internal, and open-source products published through the same approval-aware USNsoft platform used for public content and internal operations.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#product-search" class="usn-btn-primary bg-white text-slate-950 hover:bg-slate-100">Browse products</a>
                        <a href="{{ url('/client-request') }}" class="usn-btn-secondary border-white/20 bg-white/10 text-white hover:bg-white/15">Request a product conversation</a>
                    </div>
                </div>

                <div class="usn-card-dark">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Phase 1 scope</p>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="font-display text-3xl font-semibold text-white">{{ $products->total() }}</p>
                            <p class="mt-2 text-sm text-slate-300">Publicly discoverable products right now.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="font-display text-3xl font-semibold text-white">{{ $categories->count() }}</p>
                            <p class="mt-2 text-sm text-slate-300">Structured categories for search and governance.</p>
                        </div>
                    </div>
                    <ul class="mt-6 space-y-3 text-sm text-slate-300">
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                            <span>Public discovery and SEO-ready product pages.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                            <span>Protected downloads that never expose private storage paths.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                            <span>Moderated reviews tied to verified access records.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="product-search" class="usn-section">
        <div class="usn-container-wide space-y-6">
            <div class="usn-toolbar">
                <div>
                    <p class="usn-overline">Discovery</p>
                    <h2 class="usn-title mt-3">Search and filter the product catalog</h2>
                    <p class="usn-subheading">Public listing excludes private, archived, and unpublished records automatically.</p>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-300">{{ $products->total() }} result{{ $products->total() === 1 ? '' : 's' }}</p>
            </div>

            <form method="GET" action="{{ route('products.index') }}" class="usn-card">
                <div class="grid gap-4 lg:grid-cols-[2fr_repeat(4,minmax(0,1fr))]">
                    <div>
                        <x-input-label for="q" value="Keyword" />
                        <x-text-input id="q" name="q" class="mt-2 block w-full" :value="$filters['q']" placeholder="Search products, versions, or summaries" />
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

                    <div>
                        <x-input-label for="platform" value="Platform" />
                        <x-select-input id="platform" name="platform" class="mt-2 block w-full">
                            <option value="">All platforms</option>
                            @foreach ($platforms as $platform)
                                <option value="{{ $platform->value }}" @selected($filters['platform'] === $platform->value)>{{ \Illuminate\Support\Str::headline($platform->value) }}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="flex flex-col justify-end gap-3">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="featured" value="1" @checked($filters['featured']) class="usn-checkbox">
                            Featured only
                        </label>

                        <div class="flex gap-3">
                            <button type="submit" class="usn-btn-primary w-full">Apply</button>
                            <a href="{{ route('products.index') }}" class="usn-btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>

            @if ($products->count() === 0)
                <x-ui.empty-state
                    title="No products matched those filters"
                    description="Try a broader keyword, clear one of the filters, or remove the featured-only option."
                    class="usn-card"
                />
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        @php
                            $version = $product->currentPublishedVersion;
                            $featuredImage = $version?->featuredImage ?? $product->featuredImage;
                            $featuredImageUrl = $featuredImage && $featuredImage->disk === 'public'
                                ? asset('storage/'.$featuredImage->path)
                                : null;
                        @endphp

                        <article class="usn-card flex h-full flex-col overflow-hidden">
                            <div class="rounded-[1.6rem] border border-slate-200/80 bg-slate-950/5 p-4 dark:border-slate-800/80 dark:bg-slate-800/50">
                                @if ($featuredImageUrl)
                                    <img src="{{ $featuredImageUrl }}" alt="" class="h-48 w-full rounded-[1.15rem] object-cover">
                                @else
                                    <div class="flex h-48 items-center justify-center rounded-[1.15rem] bg-[linear-gradient(135deg,_#0f172a,_#0f5f92)] text-sm font-semibold uppercase tracking-[0.18em] text-white/80">
                                        {{ $product->name_current }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 flex items-center justify-between gap-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($version?->category)
                                        <span class="usn-badge-info">{{ $version->category->name }}</span>
                                    @endif
                                    @if ($product->featured_flag)
                                        <span class="usn-badge-brand">Featured</span>
                                    @endif
                                </div>
                                @if ($product->approved_review_count > 0)
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-100">{{ number_format((float) $product->average_rating, 1) }}/5</span>
                                @endif
                            </div>

                            <h2 class="mt-5 font-display text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $product->name_current }}</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-200">{{ $product->short_description_current }}</p>

                            <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                <span>{{ \Illuminate\Support\Str::headline($product->product_kind->value) }}</span>
                                @if ($product->current_version_label)
                                    <span>&middot; {{ $product->current_version_label }}</span>
                                @endif
                                @foreach ($version?->platforms ?? [] as $platform)
                                    <span>&middot; {{ \Illuminate\Support\Str::headline($platform->platform->value) }}</span>
                                @endforeach
                            </div>

                            <div class="mt-auto pt-6">
                                <a href="{{ route('products.show', ['product' => $product->slug_current]) }}" class="usn-link">View product details</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $products->links() }}
            @endif
        </div>
    </section>
</x-layouts.public>
