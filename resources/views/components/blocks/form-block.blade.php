<div class="usn-card">
    <span class="usn-badge-info">Safe form handler</span>
    <h2 class="mt-5 font-display text-2xl font-semibold text-slate-950">{{ $data['title'] ?? 'Form' }}</h2>
    @if (!empty($data['intro']))
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $data['intro'] }}</p>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-6 text-slate-700">
            <p><strong>Form type:</strong> {{ ucfirst(str_replace('_', ' ', $data['form_type'] ?? 'contact')) }}</p>
            <p class="mt-2"><strong>Anti-spam:</strong> {{ ($data['anti_spam_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}</p>
            <p class="mt-4 text-xs uppercase tracking-[0.18em] text-slate-500">No arbitrary actions</p>
        </div>

        <div class="grid gap-3">
            <div class="usn-skeleton h-12"></div>
            <div class="usn-skeleton h-12"></div>
            <div class="usn-skeleton h-28"></div>
        </div>
    </div>

    <p class="mt-5 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">This block references internal safe handlers and never exposes raw executable admin content.</p>
</div>
