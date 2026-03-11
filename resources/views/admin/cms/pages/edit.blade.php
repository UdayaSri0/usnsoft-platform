<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="font-display text-2xl font-semibold text-slate-900">Edit CMS Page</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $page->title_current }} · {{ $page->path_current }}</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-wide">
                @if ($publishedVersion)
                    <span class="rounded-lg bg-emerald-100 px-3 py-2 text-emerald-700">Live v{{ $publishedVersion->version_number }}</span>
                @else
                    <span class="rounded-lg bg-slate-200 px-3 py-2 text-slate-700">Not Published</span>
                @endif
                <span class="rounded-lg bg-amber-100 px-3 py-2 text-amber-700">Draft v{{ $draft->version_number }} · {{ $draft->workflow_state->value }}</span>
                <span class="rounded-lg bg-indigo-100 px-3 py-2 text-indigo-700">Approval: {{ $draft->approval_state->value }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif

            @if (session('preview_url'))
                <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                    Preview link generated:
                    <a href="{{ session('preview_url') }}" target="_blank" rel="noopener" class="font-semibold underline">Open preview</a>
                </div>
            @endif

            @if ($publishedVersion)
                <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Live content remains on published version v{{ $publishedVersion->version_number }} until this draft is approved and published.
                </div>
            @endif

            @include('admin.cms.pages._form', [
                'action' => route('admin.cms.pages.update', $page),
                'method' => 'PUT',
                'submitLabel' => 'Save Draft',
                'createMode' => false,
                'draft' => $draft,
            ])

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-display text-lg font-semibold text-slate-900">Workflow Actions</h2>
                <p class="mt-1 text-sm text-slate-500">Preview is required before publish. All privileged actions are audited.</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    @can('preview', $draft)
                        <form method="POST" action="{{ route('admin.cms.versions.preview', $draft) }}">
                            @csrf
                            <x-secondary-button type="submit">Generate Preview Link</x-secondary-button>
                        </form>
                    @endcan

                    @if ($draft->workflow_state === \App\Enums\ContentWorkflowState::Draft)
                        @can('submitForReview', $page)
                            <form method="POST" action="{{ route('admin.cms.pages.submit-review', $page) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Review note (optional)" class="rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <x-primary-button type="submit">Submit for Review</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if ($draft->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                        @can('approve', $page)
                            <form method="POST" action="{{ route('admin.cms.versions.approve', $draft) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Approval note" class="rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <x-primary-button type="submit">Approve</x-primary-button>
                            </form>
                        @endcan
                        @can('reject', $page)
                            <form method="POST" action="{{ route('admin.cms.versions.reject', $draft) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Rejection note" class="rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <x-danger-button type="submit">Reject to Draft</x-danger-button>
                            </form>
                        @endcan
                    @endif

                    @if ($draft->workflow_state === \App\Enums\ContentWorkflowState::Approved)
                        @can('schedule', $page)
                            <form method="POST" action="{{ route('admin.cms.versions.schedule', $draft) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="datetime-local" name="schedule_publish_at" required class="rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                <x-secondary-button type="submit">Schedule</x-secondary-button>
                            </form>
                        @endcan
                        @can('publish', $page)
                            <form method="POST" action="{{ route('admin.cms.versions.publish', $draft) }}" class="flex items-center gap-2">
                                @csrf
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" name="preview_confirmed" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                    Confirm preview done
                                </label>
                                <x-primary-button type="submit">Publish Now</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if (in_array($draft->workflow_state, [\App\Enums\ContentWorkflowState::Published, \App\Enums\ContentWorkflowState::Scheduled], true))
                        @can('archive', $page)
                            <form method="POST" action="{{ route('admin.cms.versions.archive', $draft) }}">
                                @csrf
                                <x-danger-button type="submit">Archive Version</x-danger-button>
                            </form>
                        @endcan
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
