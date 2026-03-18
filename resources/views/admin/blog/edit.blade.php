<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit Blog Post" description="{{ $post->title }} · {{ $post->slug }}" eyebrow="Content">
            <x-slot name="actions">
                <span class="usn-badge-warning">{{ $post->workflow_state->value }}</span>
                <span class="usn-badge-info">Approval: {{ $post->approval_state->value }}</span>
                @if ($post->published_at)
                    <span class="usn-badge-success">Published {{ $post->published_at->format('M j, Y') }}</span>
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            @include('admin.blog._form', [
                'action' => route('admin.blog.update', ['post' => $post->getKey()]),
                'method' => 'PUT',
                'submitLabel' => 'Save Draft',
                'createMode' => false,
                'post' => $post,
            ])

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Workflow Actions</h2>
                <p class="mt-2 text-sm text-slate-500">Only SuperAdmin can approve, schedule, publish, or archive editorial content.</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($post->workflow_state === \App\Enums\ContentWorkflowState::Draft)
                        @can('submitForReview', $post)
                            <form method="POST" action="{{ route('admin.blog.submit-review', ['post' => $post->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Review note (optional)" class="usn-input min-h-11 w-72">
                                <x-primary-button type="submit">Submit for Review</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if ($post->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                        @can('approve', $post)
                            <form method="POST" action="{{ route('admin.blog.versions.approve', ['post' => $post->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Approval note" class="usn-input min-h-11 w-72">
                                <x-primary-button type="submit">Approve</x-primary-button>
                            </form>
                        @endcan
                        @can('reject', $post)
                            <form method="POST" action="{{ route('admin.blog.versions.reject', ['post' => $post->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Revision note" class="usn-input min-h-11 w-72">
                                <x-danger-button type="submit">Reject to Draft</x-danger-button>
                            </form>
                        @endcan
                    @endif

                    @if ($post->workflow_state === \App\Enums\ContentWorkflowState::Approved)
                        @can('schedule', $post)
                            <form method="POST" action="{{ route('admin.blog.versions.schedule', ['post' => $post->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="datetime-local" name="schedule_publish_at" required class="usn-input min-h-11">
                                <input type="datetime-local" name="schedule_unpublish_at" class="usn-input min-h-11">
                                <x-secondary-button type="submit">Schedule</x-secondary-button>
                            </form>
                        @endcan
                        @can('publish', $post)
                            <form method="POST" action="{{ route('admin.blog.versions.publish', ['post' => $post->getKey()]) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" name="preview_confirmed" value="1" class="usn-checkbox">
                                    Confirm preview reviewed
                                </label>
                                <x-primary-button type="submit">Publish Now</x-primary-button>
                            </form>
                        @endcan
                    @endif

                    @if (in_array($post->workflow_state, [\App\Enums\ContentWorkflowState::Published, \App\Enums\ContentWorkflowState::Scheduled], true))
                        @can('archive', $post)
                            <form method="POST" action="{{ route('admin.blog.versions.archive', ['post' => $post->getKey()]) }}">
                                @csrf
                                <x-danger-button type="submit">Archive</x-danger-button>
                            </form>
                        @endcan

                        <a href="{{ route('blog.show', ['post' => $post->slug]) }}" target="_blank" rel="noopener" class="usn-btn-secondary">Open Public Page</a>
                    @endif
                </div>
            </section>

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Comment Governance</h2>
                <p class="mt-2 text-sm text-slate-500">Public comments remain separate from internal moderation notes and stay hidden until approved.</p>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.comments.index', ['type' => 'blog_post', 'q' => $post->title]) }}" class="usn-btn-secondary">Open Comment Moderation</a>

                    @if ($post->published_at)
                        <a href="{{ route('blog.show', ['post' => $post->slug]) }}#comments" target="_blank" rel="noopener" class="usn-btn-secondary">Open Public Comment Area</a>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
