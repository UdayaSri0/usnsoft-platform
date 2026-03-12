<x-layouts.public :seo="$seo">
    <section class="usn-section">
        <div class="usn-container-wide space-y-8">
            <a href="{{ route('careers.index') }}" class="usn-link">Back to Careers</a>

            @if (session('status') === 'application-submitted')
                <x-ui.alert tone="success" title="Application submitted">
                    Your application has been received through the protected careers workflow.
                </x-ui.alert>
            @endif

            <div class="grid gap-10 lg:grid-cols-[1fr_420px]">
                <article class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($job->department)
                            <span class="usn-badge-info">{{ $job->department }}</span>
                        @endif
                        <span class="usn-badge-warning">{{ $job->employment_type ?? 'Open role' }}</span>
                    </div>

                    <h1 class="font-display text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">{{ $job->title }}</h1>
                    <p class="text-sm text-slate-500">{{ $job->location ?? 'Location flexible' }} · {{ $job->level ?? 'Level not specified' }}</p>

                    @if ($job->summary)
                        <p class="max-w-3xl text-lg leading-8 text-slate-600">{{ $job->summary }}</p>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-3">
                        <article class="usn-card">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Deadline</p>
                            <p class="mt-3 text-sm font-semibold text-slate-900">{{ $job->deadline?->format('M j, Y g:i A') ?? 'Open until filled' }}</p>
                        </article>
                        <article class="usn-card">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Department</p>
                            <p class="mt-3 text-sm font-semibold text-slate-900">{{ $job->department ?? 'General' }}</p>
                        </article>
                        <article class="usn-card">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Type</p>
                            <p class="mt-3 text-sm font-semibold text-slate-900">{{ $job->employment_type ?? 'Not specified' }}</p>
                        </article>
                    </div>

                    <div class="usn-prose max-w-none">{!! $job->description !!}</div>
                </article>

                <aside class="usn-card">
                    <h2 class="font-display text-2xl font-semibold text-slate-950">Apply for this role</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Applicant files stay private. Only authorized staff can review records and downloads.</p>

                    @if ($job->isOpenForApplications())
                        <form method="POST" action="{{ route('careers.apply', ['job' => $job->slug]) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="full_name" value="Full name" />
                                <x-text-input id="full_name" name="full_name" class="mt-2 block w-full" :value="old('full_name')" required />
                            </div>
                            <div>
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" type="email" name="email" class="mt-2 block w-full" :value="old('email')" required />
                            </div>
                            <div>
                                <x-input-label for="phone" value="Phone" />
                                <x-text-input id="phone" name="phone" class="mt-2 block w-full" :value="old('phone')" />
                            </div>
                            <div>
                                <x-input-label for="address" value="Address" />
                                <x-textarea-input id="address" name="address" rows="3" class="mt-2 block w-full">{{ old('address') }}</x-textarea-input>
                            </div>
                            <div>
                                <x-input-label for="portfolio_url" value="Portfolio URL" />
                                <x-text-input id="portfolio_url" name="portfolio_url" class="mt-2 block w-full" :value="old('portfolio_url')" />
                            </div>
                            <div>
                                <x-input-label for="linkedin_url" value="LinkedIn URL" />
                                <x-text-input id="linkedin_url" name="linkedin_url" class="mt-2 block w-full" :value="old('linkedin_url')" />
                            </div>
                            <div>
                                <x-input-label for="github_url" value="GitHub URL" />
                                <x-text-input id="github_url" name="github_url" class="mt-2 block w-full" :value="old('github_url')" />
                            </div>
                            <div>
                                <x-input-label for="cover_message" value="Message" />
                                <x-textarea-input id="cover_message" name="cover_message" rows="5" class="mt-2 block w-full">{{ old('cover_message') }}</x-textarea-input>
                            </div>
                            <div>
                                <x-input-label for="cv" value="CV" />
                                <input id="cv" type="file" name="cv" class="mt-2 block w-full usn-file-input" required>
                            </div>
                            <div>
                                <x-input-label for="cover_letter" value="Cover letter" />
                                <input id="cover_letter" type="file" name="cover_letter" class="mt-2 block w-full usn-file-input">
                            </div>
                            <div>
                                <x-input-label for="supporting_documents" value="Supporting documents" />
                                <input id="supporting_documents" type="file" name="supporting_documents[]" multiple class="mt-2 block w-full usn-file-input">
                            </div>
                            <x-primary-button>Submit Application</x-primary-button>
                        </form>
                    @else
                        <x-ui.alert tone="warning" title="Applications closed">
                            This role is no longer accepting new applications.
                        </x-ui.alert>
                    @endif
                </aside>
            </div>
        </div>
    </section>
</x-layouts.public>
