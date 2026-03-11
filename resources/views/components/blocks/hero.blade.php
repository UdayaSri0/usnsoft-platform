<div class="grid items-center gap-10 lg:grid-cols-2">
    <div>
        @if (!empty($data['eyebrow']))
            <p class="mb-3 inline-flex items-center rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-100">
                {{ $data['eyebrow'] }}
            </p>
        @endif

        <h1 class="font-display text-3xl font-semibold leading-tight text-current sm:text-4xl lg:text-5xl">
            {{ $data['title'] ?? 'USNsoft' }}
        </h1>

        @if (!empty($data['subtitle']))
            <p class="mt-4 text-lg text-current/80">{{ $data['subtitle'] }}</p>
        @endif

        @if (!empty($data['body']))
            <p class="mt-4 max-w-2xl text-sm text-current/75 sm:text-base">{{ $data['body'] }}</p>
        @endif

        <div class="mt-7 flex flex-wrap gap-3">
            @if (!empty($data['primary_cta_label']) && !empty($data['primary_cta_url']))
                <a href="{{ $data['primary_cta_url'] }}" class="inline-flex items-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow transition hover:bg-slate-100">{{ $data['primary_cta_label'] }}</a>
            @endif
            @if (!empty($data['secondary_cta_label']) && !empty($data['secondary_cta_url']))
                <a href="{{ $data['secondary_cta_url'] }}" class="inline-flex items-center rounded-xl border border-white/40 px-5 py-3 text-sm font-semibold text-current transition hover:bg-white/10">{{ $data['secondary_cta_label'] }}</a>
            @endif
        </div>

        @if (!empty($data['trust_items']) && is_array($data['trust_items']))
            <ul class="mt-6 flex flex-wrap gap-2 text-xs text-current/80">
                @foreach ($data['trust_items'] as $item)
                    <li class="rounded-lg border border-white/20 px-3 py-1">{{ $item }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="relative hidden lg:block">
        <div class="aspect-[4/3] rounded-3xl border border-white/20 bg-gradient-to-br from-white/20 to-transparent p-6">
            <div class="h-full rounded-2xl border border-white/20 bg-white/10"></div>
        </div>
    </div>
</div>
