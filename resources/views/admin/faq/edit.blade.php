<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit FAQ" description="{{ $faq->question }}" eyebrow="Content">
            <x-slot name="actions">
                <span class="usn-badge-warning">{{ $faq->workflow_state->value }}</span>
                <span class="usn-badge-info">Approval: {{ $faq->approval_state->value }}</span>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            @include('admin.faq._form', ['faq' => $faq, 'action' => route('admin.faq.update', ['faq' => $faq->getKey()]), 'method' => 'PUT', 'submitLabel' => 'Save Draft'])

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Workflow Actions</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($faq->workflow_state === \App\Enums\ContentWorkflowState::Draft)
                        @can('submitForReview', $faq)
                            <form method="POST" action="{{ route('admin.faq.submit-review', ['faq' => $faq->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Review note" class="usn-input min-h-11 w-72">
                                <x-primary-button>Submit for Review</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if ($faq->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                        @can('approve', $faq)
                            <form method="POST" action="{{ route('admin.faq.versions.approve', ['faq' => $faq->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Approval note" class="usn-input min-h-11 w-72">
                                <x-primary-button>Approve</x-primary-button>
                            </form>
                        @endcan
                        @can('reject', $faq)
                            <form method="POST" action="{{ route('admin.faq.versions.reject', ['faq' => $faq->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Revision note" class="usn-input min-h-11 w-72">
                                <x-danger-button>Reject to Draft</x-danger-button>
                            </form>
                        @endcan
                    @endif

                    @if ($faq->workflow_state === \App\Enums\ContentWorkflowState::Approved)
                        @can('publish', $faq)
                            <form method="POST" action="{{ route('admin.faq.versions.publish', ['faq' => $faq->getKey()]) }}">
                                @csrf
                                <x-primary-button>Publish</x-primary-button>
                            </form>
                        @endcan
                        @can('schedule', $faq)
                            <form method="POST" action="{{ route('admin.faq.versions.schedule', ['faq' => $faq->getKey()]) }}" class="flex gap-2">
                                @csrf
                                <input type="datetime-local" name="schedule_publish_at" required class="usn-input min-h-11">
                                <x-secondary-button>Schedule</x-secondary-button>
                            </form>
                        @endcan
                    @endif

                    @if (in_array($faq->workflow_state, [\App\Enums\ContentWorkflowState::Published, \App\Enums\ContentWorkflowState::Scheduled], true))
                        @can('archive', $faq)
                            <form method="POST" action="{{ route('admin.faq.versions.archive', ['faq' => $faq->getKey()]) }}">
                                @csrf
                                <x-danger-button>Archive</x-danger-button>
                            </form>
                        @endcan
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
