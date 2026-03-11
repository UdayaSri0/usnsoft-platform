<div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-[radial-gradient(circle_at_top,_rgba(14,116,144,0.12),_transparent_42%),linear-gradient(180deg,_#ffffff,_#f8fafc)] p-8 text-center shadow-sm sm:p-10">
    <span class="usn-overline mx-auto">Next step</span>
    <h2 class="mt-5 font-display text-3xl font-semibold text-slate-950">{{ $data['title'] ?? 'Call to action' }}</h2>
    @if (!empty($data['body']))
        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">{{ $data['body'] }}</p>
    @endif

    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
        @if (!empty($data['primary_label']) && !empty($data['primary_url']))
            <a href="{{ $data['primary_url'] }}" class="usn-btn-primary">{{ $data['primary_label'] }}</a>
        @endif
        @if (!empty($data['secondary_label']) && !empty($data['secondary_url']))
            <a href="{{ $data['secondary_url'] }}" class="usn-btn-secondary">{{ $data['secondary_label'] }}</a>
        @endif
    </div>

    @if (!empty($data['supporting_note']))
        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $data['supporting_note'] }}</p>
    @endif
</div>
