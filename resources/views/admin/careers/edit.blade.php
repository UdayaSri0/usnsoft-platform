<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit Job Listing" description="{{ $job->title }} · {{ $job->slug }}" eyebrow="Content">
            <x-slot name="actions">
                <span class="usn-badge-warning">{{ $job->workflow_state->value }}</span>
                <span class="usn-badge-info">Approval: {{ $job->approval_state->value }}</span>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            @include('admin.careers._form', ['job' => $job, 'action' => route('admin.careers.update', ['job' => $job->getKey()]), 'method' => 'PUT', 'submitLabel' => 'Save Draft'])

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Workflow Actions</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($job->workflow_state === \App\Enums\ContentWorkflowState::Draft)
                        @can('submitForReview', $job)
                            <form method="POST" action="{{ route('admin.careers.submit-review', ['job' => $job->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Review note" class="usn-input min-h-11 w-72">
                                <x-primary-button>Submit for Review</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if ($job->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                        @can('approve', $job)
                            <form method="POST" action="{{ route('admin.careers.versions.approve', ['job' => $job->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Approval note" class="usn-input min-h-11 w-72">
                                <x-primary-button>Approve</x-primary-button>
                            </form>
                        @endcan
                        @can('reject', $job)
                            <form method="POST" action="{{ route('admin.careers.versions.reject', ['job' => $job->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Revision note" class="usn-input min-h-11 w-72">
                                <x-danger-button>Reject to Draft</x-danger-button>
                            </form>
                        @endcan
                    @endif

                    @if ($job->workflow_state === \App\Enums\ContentWorkflowState::Approved)
                        @can('publish', $job)
                            <form method="POST" action="{{ route('admin.careers.versions.publish', ['job' => $job->getKey()]) }}">
                                @csrf
                                <x-primary-button>Publish</x-primary-button>
                            </form>
                        @endcan
                        @can('schedule', $job)
                            <form method="POST" action="{{ route('admin.careers.versions.schedule', ['job' => $job->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="datetime-local" name="schedule_publish_at" required class="usn-input min-h-11">
                                <input type="datetime-local" name="schedule_unpublish_at" class="usn-input min-h-11">
                                <x-secondary-button>Schedule</x-secondary-button>
                            </form>
                        @endcan
                    @endif

                    @if (in_array($job->workflow_state, [\App\Enums\ContentWorkflowState::Published, \App\Enums\ContentWorkflowState::Scheduled], true))
                        @can('archive', $job)
                            <form method="POST" action="{{ route('admin.careers.versions.archive', ['job' => $job->getKey()]) }}">
                                @csrf
                                <x-danger-button>Archive</x-danger-button>
                            </form>
                        @endcan
                        <a href="{{ route('careers.show', ['job' => $job->slug]) }}" target="_blank" rel="noopener" class="usn-btn-secondary">Open Public Page</a>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
