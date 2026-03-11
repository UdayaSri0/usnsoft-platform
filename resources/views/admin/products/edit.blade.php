<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Edit Product"
            description="{{ $product->name_current }} · {{ $product->slug_current }}"
            eyebrow="Product Platform"
        >
            <x-slot name="actions">
                @if ($publishedVersion)
                    <span class="usn-badge-success">Live v{{ $publishedVersion->version_number }}</span>
                @else
                    <span class="usn-badge-muted">Not published</span>
                @endif
                <span class="usn-badge-warning">Draft v{{ $draft->version_number }} · {{ $draft->workflow_state->value }}</span>
                <span class="usn-badge-info">Approval: {{ $draft->approval_state->value }}</span>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            @if (session('preview_url'))
                <x-ui.alert tone="info" title="Preview link generated">
                    <a href="{{ session('preview_url') }}" target="_blank" rel="noopener" class="font-semibold underline">Open preview</a>
                </x-ui.alert>
            @endif

            @if ($publishedVersion)
                <x-ui.alert tone="warning" title="Live product remains unchanged">
                    Changes stay on the draft version until they pass review, preview confirmation, and publish.
                </x-ui.alert>
            @endif

            @include('admin.products._form', [
                'action' => route('admin.products.update', ['product' => $product->getKey()]),
                'method' => 'PUT',
                'submitLabel' => 'Save Draft',
                'createMode' => false,
                'draft' => $draft,
            ])

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Workflow Actions</h2>
                <p class="mt-2 text-sm text-slate-500">Publishing stays approval-aware. Preview confirmation remains required before publish.</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    @can('preview', $draft)
                        <form method="POST" action="{{ route('admin.products.versions.preview', ['version' => $draft->getKey()]) }}">
                            @csrf
                            <x-secondary-button type="submit">Generate Preview Link</x-secondary-button>
                        </form>
                    @endcan

                    @if ($publishedVersion && $product->visibility !== \App\Modules\Products\Enums\ProductVisibility::Private)
                        <a href="{{ route('products.show', ['product' => $product->slug_current]) }}" target="_blank" rel="noopener" class="usn-btn-secondary">Open Public Page</a>
                    @endif

                    @if ($draft->workflow_state === \App\Enums\ContentWorkflowState::Draft)
                        @can('submitForReview', $product)
                            <form method="POST" action="{{ route('admin.products.submit-review', ['product' => $product->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Review note (optional)" class="usn-input min-h-11 w-72">
                                <x-primary-button type="submit">Submit for Review</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if ($draft->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                        @can('approve', $product)
                            <form method="POST" action="{{ route('admin.products.versions.approve', ['version' => $draft->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Approval note" class="usn-input min-h-11 w-72">
                                <x-primary-button type="submit">Approve</x-primary-button>
                            </form>
                        @endcan
                        @can('reject', $product)
                            <form method="POST" action="{{ route('admin.products.versions.reject', ['version' => $draft->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Rejection note" class="usn-input min-h-11 w-72">
                                <x-danger-button type="submit">Reject to Draft</x-danger-button>
                            </form>
                        @endcan
                    @endif

                    @if ($draft->workflow_state === \App\Enums\ContentWorkflowState::Approved)
                        @can('schedule', $product)
                            <form method="POST" action="{{ route('admin.products.versions.schedule', ['version' => $draft->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="datetime-local" name="schedule_publish_at" required class="usn-input min-h-11">
                                <input type="datetime-local" name="schedule_unpublish_at" class="usn-input min-h-11">
                                <x-secondary-button type="submit">Schedule</x-secondary-button>
                            </form>
                        @endcan
                        @can('publish', $product)
                            <form method="POST" action="{{ route('admin.products.versions.publish', ['version' => $draft->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" name="preview_confirmed" value="1" class="usn-checkbox">
                                    Confirm preview done
                                </label>
                                <x-primary-button type="submit">Publish Now</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if (in_array($draft->workflow_state, [\App\Enums\ContentWorkflowState::Published, \App\Enums\ContentWorkflowState::Scheduled], true))
                        @can('archive', $product)
                            <form method="POST" action="{{ route('admin.products.versions.archive', ['version' => $draft->getKey()]) }}">
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
