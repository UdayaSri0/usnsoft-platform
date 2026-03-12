<x-layouts.public :seo="$seo">
    <section class="usn-section">
        <div class="usn-container-wide space-y-10">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
                <div>
                    <p class="usn-overline">FAQ</p>
                    <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">Frequently asked questions about products, services, and delivery.</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">Search the approved public knowledge base. Drafts, internal notes, and unpublished answers stay hidden.</p>
                </div>

                <form method="GET" class="usn-card grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="q" value="Search" />
                        <x-text-input id="q" name="q" class="mt-2 block w-full" :value="$filters['q']" placeholder="Search questions and answers" />
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
                        <x-input-label for="product" value="Product" />
                        <x-select-input id="product" name="product" class="mt-2 block w-full">
                            <option value="">Any product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->slug_current }}" @selected($filters['product'] === $product->slug_current)>{{ $product->name_current }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <x-primary-button>Apply</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($faqs as $faq)
                    <details class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 font-display text-lg font-semibold text-slate-950">
                            <span>{{ $faq->question }}</span>
                            <span class="mt-1 text-slate-400">+</span>
                        </summary>
                        <div class="mt-4 space-y-3">
                            @if ($faq->category)
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">{{ $faq->category->name }}</p>
                            @endif
                            <div class="usn-prose max-w-none">{!! $faq->answer !!}</div>
                            @if ($faq->linkedProduct)
                                <p class="text-sm text-slate-500">Related product:
                                    <a href="{{ route('products.show', ['product' => $faq->linkedProduct->slug_current]) }}" class="usn-link">{{ $faq->linkedProduct->name_current }}</a>
                                </p>
                            @endif
                        </div>
                    </details>
                @empty
                    <x-ui.empty-state title="No FAQs published yet" description="Approved answers will appear here once they are publicly available." />
                @endforelse
            </div>

            <div>{{ $faqs->links() }}</div>
        </div>
    </section>
</x-layouts.public>
