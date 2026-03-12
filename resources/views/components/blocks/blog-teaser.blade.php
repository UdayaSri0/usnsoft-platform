@php
    $limit = max(1, (int) ($data['item_limit'] ?? 3));
    $posts = array_slice([
        [
            'category' => 'Security',
            'title' => 'Operational hardening for approval-driven publishing',
            'excerpt' => 'A practical approach to previews, approvals, and privileged publishing without weakening staff boundaries.',
            'author' => 'USNsoft Editorial',
        ],
        [
            'category' => 'Platform',
            'title' => 'Designing one Laravel codebase for both public and internal experiences',
            'excerpt' => 'How we keep public polish and internal efficiency aligned through shared tokens, layouts, and safe content primitives.',
            'author' => 'Engineering Team',
        ],
        [
            'category' => 'Delivery',
            'title' => 'Why enterprise request intake needs stronger state clarity',
            'excerpt' => 'Fast-feeling UX depends on explicit validation, empty states, and workflow visibility from first contact onward.',
            'author' => 'Operations Team',
        ],
        [
            'category' => 'Architecture',
            'title' => 'Queue, audit, and scheduled publishing in a modern corporate platform',
            'excerpt' => 'The operational layers that keep notifications, content transitions, and traceability reliable over time.',
            'author' => 'Platform Engineering',
        ],
    ], 0, $limit);
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading
        eyebrow="Insights"
        :title="$data['title'] ?? 'Blog & News'"
        :intro="$data['intro'] ?? 'Recent thinking from the USNsoft platform, security, and delivery teams.'"
    />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($posts as $index => $post)
            <article class="usn-card flex h-full flex-col">
                <div class="flex items-center justify-between gap-3">
                    <span class="usn-badge-info">{{ $post['category'] }}</span>
                    @if (($data['show_date'] ?? true) === true)
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ now()->subDays($index * 4)->format('M d, Y') }}</p>
                    @endif
                </div>

                <h3 class="mt-5 font-display text-xl font-semibold text-slate-950 dark:text-slate-50">{{ $post['title'] }}</h3>

                @if (($data['show_author'] ?? true) === true)
                    <p class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $post['author'] }}</p>
                @endif

                @if (($data['show_excerpt'] ?? true) === true)
                    <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $post['excerpt'] }}</p>
                @endif

                <div class="mt-auto pt-6">
                    <a href="{{ url('/blog') }}" class="usn-link">Read update</a>
                </div>
            </article>
        @endforeach
    </div>
</div>
