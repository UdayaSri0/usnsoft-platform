@php
    $contactItems = array_filter([
        ['label' => 'Email', 'value' => $data['email'] ?? 'hello@usnsoft.test'],
        ['label' => 'Phone', 'value' => $data['phone'] ?? '+1 (555) 010-2026'],
        ['label' => 'Hours', 'value' => $data['hours'] ?? 'Monday to Friday, 09:00 to 18:00'],
        ['label' => 'Address', 'value' => $data['address'] ?? 'Colombo delivery hub with distributed engineering support'],
    ], static fn (array $item): bool => filled($item['value']));

    $targetUrl = ($data['form_type'] ?? 'contact') === 'project_inquiry'
        ? url('/client-request')
        : url('/contact');
@endphp

<div class="grid gap-6 lg:grid-cols-[0.92fr_1.08fr]">
    <div class="space-y-6">
        <x-ui.public.section-heading
            eyebrow="Contact"
            :title="$data['title'] ?? 'Talk to the USNsoft team'"
            :intro="$data['intro'] ?? 'Share your delivery goals and we will route you to the right engineering, security, or operations lead.'"
        />

        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach ($contactItems as $item)
                <div class="usn-stat-card">
                    <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $item['label'] }}</dt>
                    <dd class="mt-3 text-sm leading-6 text-slate-800">{{ $item['value'] }}</dd>
                </div>
            @endforeach
        </dl>

        @if (!empty($data['whatsapp_url']) || !empty($data['telegram_url']))
            <div class="flex flex-wrap gap-3">
                @if (!empty($data['whatsapp_url']))
                    <a href="{{ $data['whatsapp_url'] }}" class="usn-btn-secondary">WhatsApp</a>
                @endif
                @if (!empty($data['telegram_url']))
                    <a href="{{ $data['telegram_url'] }}" class="usn-btn-secondary">Telegram</a>
                @endif
            </div>
        @endif
    </div>

    <div class="usn-card">
        <span class="usn-badge-brand">Secure intake</span>
        <h3 class="mt-5 font-display text-2xl font-semibold text-slate-950">Clear next steps for new enquiries</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            Public form actions remain limited to safe internal handlers. This avoids arbitrary external endpoints while keeping the experience straightforward for customers and staff.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">1</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Submit requirements</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Capture scope, urgency, and key constraints.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">2</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Internal triage</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Route to the correct delivery lead without losing context.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">3</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Structured response</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Reply with a clear plan, timing, and follow-up path.</p>
            </div>
        </div>

        @if (($data['show_form'] ?? false) === true)
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $targetUrl }}" class="usn-btn-primary">
                    {{ ucfirst(str_replace('_', ' ', $data['form_type'] ?? 'contact')) }}
                </a>
                <a href="{{ url('/faq') }}" class="usn-btn-secondary">See common questions</a>
            </div>
        @else
            <p class="mt-6 text-sm text-slate-600">The form panel is hidden for this section. Use the direct contact details instead.</p>
        @endif
    </div>
</div>
