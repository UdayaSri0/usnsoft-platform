@php
    $limit = max(1, (int) ($data['item_limit'] ?? 3));
    $canQueryProducts = \Illuminate\Support\Facades\Schema::hasTable('products')
        && \Illuminate\Support\Facades\Schema::hasTable('product_versions');
    $products = collect();

    if ($canQueryProducts) {
        $products = \App\Modules\Products\Models\Product::query()
            ->publicCatalog()
            ->with(['currentPublishedVersion.category', 'currentPublishedVersion.platforms'])
            ->orderByDesc('featured_flag')
            ->orderBy('name_current')
            ->limit($limit)
            ->get();
    }

    if ($products->isEmpty()) {
        $products = collect(array_slice([
            [
                'family' => 'Platform',
                'name' => 'USNsoft Commerce Core',
                'summary' => 'A secure product foundation for multi-team websites, request workflows, and customer-facing portals.',
                'highlights' => 'Laravel 12, approvals, protected access',
                'url' => url('/products'),
            ],
            [
                'family' => 'Security',
                'name' => 'Operational Security Suite',
                'summary' => 'Visibility into sessions, devices, verification, and high-risk actions across internal and external user journeys.',
                'highlights' => 'Audit trails, alerting, policy enforcement',
                'url' => url('/products'),
            ],
            [
                'family' => 'Infrastructure',
                'name' => 'Delivery Automation Layer',
                'summary' => 'Queues, scheduled publishing, and environment discipline aligned with enterprise maintenance expectations.',
                'highlights' => 'Redis, scheduler, Docker-first workflow',
                'url' => url('/products'),
            ],
        ], 0, $limit));
    }
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading
        eyebrow="Products"
        :title="$data['title'] ?? 'Platform products and delivery layers'"
        :intro="$data['intro'] ?? 'Core offerings that support secure digital delivery without splitting the platform into disconnected systems.'"
    />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($products as $product)
            <article class="usn-card flex h-full flex-col">
                <div class="flex items-center justify-between gap-3">
                    <span class="usn-badge-info">{{ data_get($product, 'currentPublishedVersion.category.name', data_get($product, 'family', 'Product')) }}</span>
                    @if (($data['show_platforms'] ?? true) === true)
                        <span class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            @php
                                $platforms = collect(data_get($product, 'currentPublishedVersion.platforms', []))
                                    ->map(fn ($platform) => \Illuminate\Support\Str::headline($platform->platform->value))
                                    ->take(2)
                                    ->implode(' + ');
                            @endphp
                            {{ $platforms !== '' ? $platforms : 'Web + Internal' }}
                        </span>
                    @endif
                </div>

                <h3 class="mt-5 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ data_get($product, 'name_current', data_get($product, 'name')) }}</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ data_get($product, 'short_description_current', data_get($product, 'summary')) }}</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                    {{ data_get($product, 'current_version_label') ?: data_get($product, 'highlights') }}
                </p>

                @if (($data['show_cta'] ?? true) === true)
                    <div class="mt-auto pt-6">
                        <a href="{{ data_get($product, 'slug_current') ? route('products.show', ['product' => data_get($product, 'slug_current')]) : data_get($product, 'url', url('/products')) }}" class="usn-link">View product details</a>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</div>
