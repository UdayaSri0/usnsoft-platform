<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Applicant Review" description="{{ $application->full_name }} · {{ $application->job?->title }}" eyebrow="Operations">
            <x-slot name="actions">
                <span class="usn-badge-info">{{ \Illuminate\Support\Str::headline($application->status->value) }}</span>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="grid gap-6 lg:grid-cols-[1fr_360px]">
                <div class="usn-card space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Full name</p>
                            <p class="mt-2 text-base text-slate-900">{{ $application->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email</p>
                            <p class="mt-2 text-base text-slate-900">{{ $application->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Phone</p>
                            <p class="mt-2 text-base text-slate-900">{{ $application->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Submitted</p>
                            <p class="mt-2 text-base text-slate-900">{{ $application->submitted_at?->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>

                    @if ($application->address)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Address</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application->address }}</p>
                        </div>
                    @endif

                    @if ($application->cover_message)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cover message</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $application->cover_message }}</p>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-3">
                        @if ($application->portfolio_url)
                            <a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener" class="usn-card-link p-4">
                                <p class="text-sm font-semibold text-slate-900">Portfolio</p>
                            </a>
                        @endif
                        @if ($application->linkedin_url)
                            <a href="{{ $application->linkedin_url }}" target="_blank" rel="noopener" class="usn-card-link p-4">
                                <p class="text-sm font-semibold text-slate-900">LinkedIn</p>
                            </a>
                        @endif
                        @if ($application->github_url)
                            <a href="{{ $application->github_url }}" target="_blank" rel="noopener" class="usn-card-link p-4">
                                <p class="text-sm font-semibold text-slate-900">GitHub</p>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Files</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($application->files as $file)
                                <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $file->original_name }}</p>
                                        <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::headline($file->file_type) }} · {{ number_format($file->size_bytes / 1024, 1) }} KB</p>
                                    </div>
                                    <a href="{{ route('admin.careers.applications.files.download', ['file' => $file->getKey()]) }}" class="usn-link">Download</a>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Update Status</h2>
                        <form method="POST" action="{{ route('admin.careers.applications.status.update', ['application' => $application->getKey()]) }}" class="mt-4 space-y-3">
                            @csrf
                            @method('PUT')
                            <x-select-input name="status" class="block w-full">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected($application->status === $status)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                                @endforeach
                            </x-select-input>
                            <x-textarea-input name="note" rows="3" class="block w-full" placeholder="Internal note for this change"></x-textarea-input>
                            <x-primary-button>Update Status</x-primary-button>
                        </form>
                    </section>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Internal Notes</h2>
                    <form method="POST" action="{{ route('admin.careers.applications.notes.store', ['application' => $application->getKey()]) }}" class="mt-4 space-y-3">
                        @csrf
                        <x-textarea-input name="note_body" rows="4" class="block w-full" placeholder="Add an internal review note"></x-textarea-input>
                        <x-primary-button>Add Note</x-primary-button>
                    </form>

                    <div class="mt-5 space-y-4">
                        @foreach ($application->notes as $note)
                            <article class="rounded-2xl border border-slate-200 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ $note->author?->name ?? 'Staff' }} · {{ $note->created_at?->format('M j, Y g:i A') }}</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $note->note_body }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Status History</h2>
                    <div class="mt-5 space-y-4">
                        @foreach ($application->statusHistories as $history)
                            <article class="rounded-2xl border border-slate-200 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ \Illuminate\Support\Str::headline($history->to_state) }} · {{ $history->changed_at?->format('M j, Y g:i A') }}</p>
                                <p class="mt-2 text-sm text-slate-700">From {{ \Illuminate\Support\Str::headline($history->from_state ?? 'new') }} to {{ \Illuminate\Support\Str::headline($history->to_state) }}</p>
                                @if ($history->reason)
                                    <p class="mt-2 text-sm text-slate-500">{{ $history->reason }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            </section>
        </div>
    </div>
</x-app-layout>
