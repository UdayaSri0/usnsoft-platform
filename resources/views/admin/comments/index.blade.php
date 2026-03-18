<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Comment Moderation"
            description="Public comments remain pending until an authorized internal reviewer approves them."
            eyebrow="Moderation"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <form method="GET" action="{{ route('admin.comments.index') }}" class="usn-toolbar">
                <div class="grid flex-1 gap-3 md:grid-cols-5">
                    <x-text-input name="q" :value="$filters['q']" placeholder="Search comments, authors, targets" />
                    <x-select-input name="type">
                        <option value="">All targets</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="status">
                        <option value="">All moderation states</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-text-input name="date_from" type="date" :value="$filters['dateFrom']" />
                    <x-text-input name="date_to" type="date" :value="$filters['dateTo']" />
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="usn-btn-primary">Filter</button>
                    <a href="{{ route('admin.comments.index') }}" class="usn-btn-secondary">Reset</a>
                </div>
            </form>

            <div class="space-y-4">
                @forelse ($comments as $comment)
                    <article class="usn-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="usn-badge-info">{{ $comment->targetLabel() }}</span>
                                    <span class="usn-badge-warning">{{ \Illuminate\Support\Str::headline($comment->status->value) }}</span>
                                </div>
                                <h2 class="mt-4 font-display text-xl font-semibold text-slate-950">{{ $comment->targetTitle() }}</h2>
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->body }}</p>
                                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    {{ $comment->user?->name ?? 'Unknown user' }} · {{ $comment->user?->email ?? 'unknown' }}
                                    @if ($comment->submitted_at)
                                        · submitted {{ $comment->submitted_at->format('M j, Y g:i A') }}
                                    @endif
                                </p>
                                @can('viewInternalNotes', $comment)
                                    @if ($comment->moderation_reason)
                                        <p class="mt-3 text-sm text-slate-500">Moderation note: {{ $comment->moderation_reason }}</p>
                                    @endif
                                @endcan
                            </div>

                            <form method="POST" action="{{ route('admin.comments.moderate', ['comment' => $comment->getKey()]) }}" class="w-full max-w-md rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label :for="'comment-status-'.$comment->getKey()" value="Moderation State" />
                                        <x-select-input :id="'comment-status-'.$comment->getKey()" name="status" class="mt-2 block w-full">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->value }}" @selected($comment->status === $status)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                                            @endforeach
                                        </x-select-input>
                                    </div>
                                    <div>
                                        <x-input-label :for="'comment-reason-'.$comment->getKey()" value="Internal Moderation Note" />
                                        @can('viewInternalNotes', $comment)
                                            <x-textarea-input :id="'comment-reason-'.$comment->getKey()" name="moderation_reason" rows="3" class="mt-2 block w-full">{{ $comment->moderation_reason }}</x-textarea-input>
                                        @else
                                            <p class="mt-2 text-sm text-slate-500">Internal moderation notes are permission-restricted.</p>
                                        @endcan
                                    </div>
                                    <button type="submit" class="usn-btn-primary w-full">Apply Moderation</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No comments found" description="Pending and moderated public comments will appear here once enabled content starts receiving submissions." class="usn-card" />
                @endforelse
            </div>

            {{ $comments->links() }}
        </div>
    </div>
</x-app-layout>
