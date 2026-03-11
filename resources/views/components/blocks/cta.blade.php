<div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
    <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Call to action' }}</h2>
    @if (!empty($data['body']))
        <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">{{ $data['body'] }}</p>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        @if (!empty($data['primary_label']) && !empty($data['primary_url']))
            <a href="{{ $data['primary_url'] }}" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow transition hover:bg-slate-800">{{ $data['primary_label'] }}</a>
        @endif
        @if (!empty($data['secondary_label']) && !empty($data['secondary_url']))
            <a href="{{ $data['secondary_url'] }}" class="inline-flex items-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400">{{ $data['secondary_label'] }}</a>
        @endif
    </div>

    @if (!empty($data['supporting_note']))
        <p class="mt-4 text-xs uppercase tracking-wide text-slate-500">{{ $data['supporting_note'] }}</p>
    @endif
</div>
