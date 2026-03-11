@php
    $trustItems = is_array($data['trust_items'] ?? null) && $data['trust_items'] !== []
        ? array_slice($data['trust_items'], 0, 6)
        : [
            'Approval-driven publishing',
            'Role-aware access control',
            'Queue and audit ready',
        ];
@endphp

<div class="grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
    <div>
        @if (!empty($data['eyebrow']))
            <p class="mb-4 inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-cyan-100">
                {{ $data['eyebrow'] }}
            </p>
        @endif

        @if (!empty($data['badge']))
            <p class="mb-4">
                <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/80">{{ $data['badge'] }}</span>
            </p>
        @endif

        <h1 class="font-display text-4xl font-semibold leading-tight text-current sm:text-5xl lg:text-6xl">
            {{ $data['title'] ?? 'USNsoft' }}
        </h1>

        @if (!empty($data['subtitle']))
            <p class="mt-5 max-w-2xl text-lg leading-8 text-current/80 sm:text-xl">{{ $data['subtitle'] }}</p>
        @endif

        @if (!empty($data['body']))
            <p class="mt-4 max-w-2xl text-sm leading-7 text-current/75 sm:text-base">{{ $data['body'] }}</p>
        @endif

        <div class="mt-8 flex flex-wrap gap-3">
            @if (!empty($data['primary_cta_label']) && !empty($data['primary_cta_url']))
                <a href="{{ $data['primary_cta_url'] }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-slate-100">{{ $data['primary_cta_label'] }}</a>
            @endif
            @if (!empty($data['secondary_cta_label']) && !empty($data['secondary_cta_url']))
                <a href="{{ $data['secondary_cta_url'] }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-white/25 px-5 py-3 text-sm font-semibold text-current transition hover:bg-white/10">{{ $data['secondary_cta_label'] }}</a>
            @endif
        </div>

        <ul class="mt-8 grid gap-3 sm:grid-cols-3">
            @foreach ($trustItems as $item)
                <li class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm leading-6 text-white/85">{{ $item }}</li>
            @endforeach
        </ul>
    </div>

    <div class="relative">
        <div class="absolute inset-8 rounded-full bg-cyan-400/15 blur-3xl"></div>
        <div class="relative rounded-[2rem] border border-white/15 bg-white/10 p-6 backdrop-blur">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-slate-950/20 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Delivery lanes</p>
                    <p class="mt-3 font-display text-3xl font-semibold">03</p>
                    <p class="mt-2 text-sm text-white/75">Software, networking, and security execution in one operating model.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-950/20 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Visibility</p>
                    <p class="mt-3 font-display text-3xl font-semibold">24/7</p>
                    <p class="mt-2 text-sm text-white/75">State clarity across requests, approvals, and protected access flows.</p>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/20 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Execution model</p>
                <ol class="mt-4 space-y-3 text-sm text-white/80">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                        <span>Translate requirements into structured delivery tracks and ownership.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                        <span>Protect privileged actions with review, approval, and audit trails.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-cyan-300"></span>
                        <span>Keep public and internal experiences aligned through shared design primitives.</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
