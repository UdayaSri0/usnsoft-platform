<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="font-display text-2xl font-semibold text-slate-900">{{ $data['title'] ?? 'Form' }}</h2>
    @if (!empty($data['intro']))
        <p class="mt-2 text-sm text-slate-600">{{ $data['intro'] }}</p>
    @endif

    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
        Form type: <strong>{{ ucfirst(str_replace('_', ' ', $data['form_type'] ?? 'contact')) }}</strong>
        <br>
        Anti-spam: <strong>{{ ($data['anti_spam_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}</strong>
    </div>

    <p class="mt-4 text-xs text-slate-500">This block references internal safe handlers and does not allow arbitrary external form actions.</p>
</div>
