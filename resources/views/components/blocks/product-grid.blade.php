<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Products' }}</h2>
        @if (!empty($data['intro']))
            <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @for ($i = 0; $i < ((int) ($data['item_limit'] ?? 3)); $i++)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Product</p>
                <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Product teaser {{ $i + 1 }}</h3>
                <p class="mt-2 text-sm text-slate-600">Product module integration can bind real records in the next stage.</p>
            </article>
        @endfor
    </div>
</div>
