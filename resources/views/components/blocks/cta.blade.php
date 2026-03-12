<div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-[radial-gradient(circle_at_top,_rgba(14,116,144,0.12),_transparent_42%),linear-gradient(180deg,_#ffffff,_#f8fafc)] p-8 text-center shadow-sm dark:border-slate-800/80 dark:bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_42%),linear-gradient(180deg,_rgba(15,23,42,0.95),_rgba(2,6,23,0.98))] sm:p-10">
    <span class="usn-overline mx-auto">Next step</span>
    <h2 class="mt-5 font-display text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ $data['title'] ?? 'Call to action' }}</h2>
    @if (!empty($data['body']))
        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">{{ $data['body'] }}</p>
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
        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $data['supporting_note'] }}</p>
    @endif
</div>
