<x-layouts.public :seo="$seo">
    <section class="usn-section">
        <div class="usn-container-wide space-y-10">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
                <div>
                    <p class="usn-overline">Careers</p>
                    <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">Join a team building secure, approval-aware digital platforms.</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">Open roles remain public only after approval and stay submission-aware by deadline.</p>
                </div>

                <form method="GET" class="usn-card grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="q" value="Search" />
                        <x-text-input id="q" name="q" class="mt-2 block w-full" :value="$filters['q']" placeholder="Role, department, or location" />
                    </div>
                    <div>
                        <x-input-label for="department" value="Department" />
                        <x-select-input id="department" name="department" class="mt-2 block w-full">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}" @selected($filters['department'] === $department)>{{ $department }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div>
                        <x-input-label for="employment_type" value="Employment type" />
                        <x-select-input id="employment_type" name="employment_type" class="mt-2 block w-full">
                            <option value="">All types</option>
                            @foreach ($employmentTypes as $employmentType)
                                <option value="{{ $employmentType }}" @selected($filters['employment_type'] === $employmentType)>{{ $employmentType }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="location" value="Location" />
                        <x-select-input id="location" name="location" class="mt-2 block w-full">
                            <option value="">Any location</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location }}" @selected($filters['location'] === $location)>{{ $location }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <x-primary-button>Apply Filters</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
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
                        <h2 class="mt-5 font-display text-2xl font-semibold text-slate-950">{{ $job->title }}</h2>
                        <p class="mt-3 text-sm text-slate-500">{{ $job->employment_type ?? 'Role type not specified' }} · {{ $job->location ?? 'Location flexible' }}</p>
                        @if ($job->summary)
                            <p class="mt-4 text-sm leading-6 text-slate-600">{{ $job->summary }}</p>
                        @endif
                        <div class="mt-auto pt-6 flex items-center justify-between gap-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ $job->deadline ? 'Apply by '.$job->deadline->format('M j, Y') : 'Open until filled' }}</p>
                            <a href="{{ route('careers.show', ['job' => $job->slug]) }}" class="usn-link">View role</a>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No open roles right now" description="Approved roles will appear here when hiring is active." class="lg:col-span-2" />
                @endforelse
            </div>

            <div>{{ $jobs->links() }}</div>
        </div>
    </section>
</x-layouts.public>
