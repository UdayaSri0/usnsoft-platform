@php
    $seo = [
        'meta_title' => $download->label.' | Controlled Access | '.config('app.name', 'USNsoft'),
        'meta_description' => 'This product delivery path uses a controlled request workflow instead of exposing a public file.',
        'canonical_url' => url()->current(),
        'robots_index' => false,
        'robots_follow' => false,
    ];
    $clientRequestUrl = auth()->check() && auth()->user()->can('requests.create')
        ? route('client-requests.create')
        : url('/client-request');
@endphp

<x-layouts.public :seo="$seo">
    <section class="usn-section-lg">
        <div class="usn-container-narrow space-y-6">
            <x-ui.alert tone="info" title="Controlled delivery path">
                This download uses <strong>manual request mode</strong>. The platform records the authenticated access attempt, but it does not expose a fake public file.
            </x-ui.alert>

            <div class="usn-card">
                <span class="usn-badge-info">{{ $product->name_current }}</span>
                <h1 class="mt-5 font-display text-3xl font-semibold text-slate-950">{{ $download->label }}</h1>
                <p class="mt-4 text-base leading-7 text-slate-600">{{ $download->description ?: 'A controlled handoff is required for this release.' }}</p>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Authenticated user</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $user->email }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Version</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $download->version_label ?: $product->current_version_label ?: 'Current release' }}</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $clientRequestUrl }}" class="usn-btn-primary">Open client request</a>
                    <a href="{{ route('products.show', ['product' => $product->slug_current]) }}" class="usn-btn-secondary">Back to product</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
