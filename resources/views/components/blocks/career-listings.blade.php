@php
    $sourceMode = (string) ($data['source_mode'] ?? 'open');
    $limit = max(1, (int) ($data['item_limit'] ?? 6));
    $jobs = app(\App\Modules\Careers\Services\CareerCatalogService::class)->openJobs(
        limit: $limit,
        featuredOnly: $sourceMode === 'featured',
        department: (string) ($data['department'] ?? ''),
        employmentType: (string) ($data['employment_type'] ?? ''),
    );
@endphp

<div class="space-y-8">
    <x-ui.public.section-heading
        eyebrow="Careers"
        :title="$data['title'] ?? 'Open positions'"
        :intro="$data['intro'] ?? 'Approved vacancies from the protected careers workflow.'"
    />

    <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        @forelse ($jobs as $job)
            <article class="usn-card flex h-full flex-col">
                <div class="flex flex-wrap items-center gap-3">
                    @if ($job->featured_flag)
                        <span class="usn-badge-warning">Featured</span>
                    @endif
                    @if ($job->department)
                        <span class="usn-badge-info">{{ $job->department }}</span>
                    @endif
                </div>

                <h3 class="mt-5 font-display text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $job->title }}</h3>

                @if (($data['show_location'] ?? true) === true)
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ $job->employment_type ?? 'Open role' }} · {{ $job->location ?? 'Location flexible' }}</p>
                @endif

                @if (($data['show_summary'] ?? true) === true && $job->summary)
                    <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $job->summary }}</p>
                @endif

                <div class="mt-auto flex items-center justify-between gap-3 pt-6">
                    @if (($data['show_deadline'] ?? true) === true)
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            {{ $job->deadline ? 'Apply by '.$job->deadline->format('M j, Y') : 'Open until filled' }}
                        </p>
                    @endif
                    <a href="{{ route('careers.show', ['job' => $job->slug]) }}" class="usn-link">View role</a>
                </div>
            </article>
        @empty
            <x-ui.empty-state title="No open roles available" description="Publish approved job listings to populate this careers block." class="lg:col-span-2 xl:col-span-3" />
        @endforelse
    </div>
</div>
