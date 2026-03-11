@php
    $services = [
        [
            'title' => 'Software delivery',
            'body' => 'Laravel application design, workflow implementation, secure customer access, and long-term maintainability planning.',
        ],
        [
            'title' => 'Network and infrastructure',
            'body' => 'Operational networking support, deployment topology decisions, observability, and runtime stability improvements.',
        ],
        [
            'title' => 'Security operations',
            'body' => 'Authorization boundaries, auditability, privileged action controls, and recovery-minded operational runbooks.',
        ],
    ];
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading
        eyebrow="Services"
        :title="$data['title'] ?? 'Services'"
        :intro="$data['intro'] ?? 'Cross-functional delivery that keeps customer experience, internal efficiency, and security posture aligned.'"
    />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($services as $service)
            <article class="usn-card h-full">
                <div class="usn-icon-chip">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 12h14M12 5v14" />
                    </svg>
                </div>
                <h3 class="mt-5 font-display text-xl font-semibold text-slate-950">{{ $service['title'] }}</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $service['body'] }}</p>
            </article>
        @endforeach
    </div>

    @if (!empty($data['cta_label']) && !empty($data['cta_url']))
        <a href="{{ $data['cta_url'] }}" class="usn-btn-primary">{{ $data['cta_label'] }}</a>
    @endif
</div>
