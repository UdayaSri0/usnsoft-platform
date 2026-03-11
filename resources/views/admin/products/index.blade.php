<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Products"
            description="Publish structured product pages, version details, moderated reviews, and protected downloads without splitting public and internal systems."
            eyebrow="Product Platform"
        >
            <x-slot name="actions">
                @if (auth()->user()->hasPermission('products.reviews.moderate'))
                    <a href="{{ route('admin.products.reviews.index') }}" class="usn-btn-secondary">Review Moderation</a>
                @endif
                @if (auth()->user()->hasPermission('products.categories.manage'))
                    <a href="{{ route('admin.products.categories.index') }}" class="usn-btn-secondary">Categories</a>
                @endif
                @if (auth()->user()->hasPermission('products.tags.manage'))
                    <a href="{{ route('admin.products.tags.index') }}" class="usn-btn-secondary">Tags</a>
                @endif
                @can('create', \App\Modules\Products\Models\Product::class)
                    <a href="{{ route('admin.products.create') }}" class="usn-btn-primary">New Product</a>
                @endcan
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <form method="GET" action="{{ route('admin.products.index') }}" class="usn-toolbar">
                <div class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <x-text-input name="q" :value="$filters['q']" placeholder="Search products" />

                    <x-select-input name="status">
                        <option value="">All workflow states</option>
                        @foreach ($workflowStates as $state)
                            <option value="{{ $state->value }}" @selected($filters['status'] === $state->value)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                        @endforeach
                    </x-select-input>

                    <x-select-input name="visibility">
                        <option value="">All visibilities</option>
                        @foreach ($visibilityStates as $state)
                            <option value="{{ $state->value }}" @selected($filters['visibility'] === $state->value)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                        @endforeach
                    </x-select-input>

                    <x-select-input name="category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </x-select-input>

                    <x-select-input name="featured">
                        <option value="">Any featured state</option>
                        <option value="1" @selected($filters['featured'] === '1')>Featured only</option>
                        <option value="0" @selected($filters['featured'] === '0')>Non-featured</option>
                    </x-select-input>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="usn-btn-primary">Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="usn-btn-secondary">Reset</a>
                </div>
            </form>

            <div class="usn-table-shell">
                <div class="usn-table-scroll">
                    <table class="usn-table">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Visibility</th>
                                <th class="px-4 py-3">Draft</th>
                                <th class="px-4 py-3">Published</th>
                                <th class="px-4 py-3">Reviews</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($products as $product)
                                @php
                                    $draft = $product->currentDraftVersion;
                                    $published = $product->currentPublishedVersion;
                                    $displayCategory = $draft?->category?->name ?? $published?->category?->name;
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">{{ $product->name_current }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $displayCategory ?: 'Uncategorized' }}
                                            &middot; {{ \Illuminate\Support\Str::headline($product->product_kind->value) }}
                                            @if ($product->featured_flag)
                                                &middot; Featured
                                            @endif
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="usn-badge-muted">{{ \Illuminate\Support\Str::headline($product->visibility->value) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($draft)
                                            <span class="usn-badge-warning">v{{ $draft->version_number }} · {{ $draft->workflow_state->value }}</span>
                                        @else
                                            <span class="text-xs text-slate-500">No draft</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($published)
                                            <span class="usn-badge-success">v{{ $published->version_number }} · {{ $published->current_version ?: 'Published' }}</span>
                                        @else
                                            <span class="text-xs text-slate-500">Not published</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $product->approved_review_count }} approved
                                        @if ($product->average_rating)
                                            <span class="text-slate-400">&middot;</span> {{ number_format((float) $product->average_rating, 1) }}/5
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3 text-sm font-semibold">
                                            <a href="{{ route('admin.products.edit', ['product' => $product->getKey()]) }}" class="text-sky-700 hover:text-sky-900">Open editor</a>
                                            @if ($published && $product->visibility !== \App\Modules\Products\Enums\ProductVisibility::Private)
                                                <a href="{{ route('products.show', ['product' => $product->slug_current]) }}" class="text-slate-600 hover:text-slate-900" target="_blank" rel="noopener">Public view</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No products have been created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
