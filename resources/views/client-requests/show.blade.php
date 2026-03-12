<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$projectRequest->project_title"
            :description="$projectRequest->project_summary"
            eyebrow="Client Request"
        >
            <x-slot name="actions">
                <x-client-request-status-badge :status="$projectRequest->currentStatus" />
                <a href="{{ route('client-requests.index') }}" class="usn-btn-secondary">Back to Requests</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status') === 'project-request-comment-created')
                <x-ui.alert tone="success" title="Comment added">
                    Your update is now visible in the request discussion and available to staff reviewers.
                </x-ui.alert>
            @endif

            <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <div class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Request summary</h2>
                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach ($displaySections as $section)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $section['label'] }}</dt>
                                    <dd class="mt-2 text-sm text-slate-800">{{ $section['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-6 space-y-5">
                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Description</h3>
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $projectRequest->project_description }}</p>
                            </div>

                            @foreach ([
                                'Requested Features' => $projectRequest->requested_features,
                                'Preferred Tech Stack' => $projectRequest->preferred_tech_stack,
                                'Preferred Meeting Availability' => $projectRequest->preferred_meeting_availability,
                            ] as $label => $items)
                                @if (! empty($items))
                                    <div>
                                        <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $label }}</h3>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($items as $item)
                                                <span class="usn-badge-muted">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Protected attachments</h2>
                        <p class="mt-2 text-sm text-slate-600">Only you and authorized staff can access these files.</p>

                        <div class="mt-5 space-y-3">
                            @forelse ($visibleAttachments as $attachment)
                                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-muted">{{ $attachment->category->label() }}</span>
                                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $attachment->malware_scan_status->label() }}</span>
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-900">{{ $attachment->original_name }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Number::fileSize($attachment->size_bytes) }} · {{ $attachment->mime_type ?: 'Unknown type' }}</p>
                                    </div>
                                    <a href="{{ route('client-requests.attachments.show', ['projectRequest' => $projectRequest, 'attachment' => $attachment]) }}" class="usn-btn-secondary">Download</a>
                                </div>
                            @empty
                                <x-ui.empty-state title="No visible files" description="Attachments uploaded to this request will appear here once available to you." />
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Visible timeline</h2>
                        <p class="mt-2 text-sm text-slate-600">This feed only shows statuses and updates intentionally visible to you.</p>

                        <div class="mt-5 space-y-4">
                            @forelse ($timeline as $item)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-muted">{{ \Illuminate\Support\Str::headline($item['kind']) }}</span>
                                            <h3 class="font-semibold text-slate-900">{{ $item['title'] }}</h3>
                                        </div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ optional($item['occurred_at'])->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $item['body'] }}</p>
                                    @if (! empty($item['note']))
                                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $item['note'] }}</p>
                                    @endif
                                </article>
                            @empty
                                <x-ui.empty-state title="No visible updates yet" description="Status changes and requester-visible comments will appear here as staff review your request." />
                            @endforelse
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Visible discussion</h2>
                        <p class="mt-2 text-sm text-slate-600">Internal-only staff comments never appear in this section.</p>

                        <div class="mt-5 space-y-4">
                            @forelse ($requesterVisibleComments as $comment)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <p class="font-semibold text-slate-900">{{ $comment->author?->name ?? 'USNsoft Team' }}</p>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $comment->created_at?->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->body }}</p>
                                </article>
                            @empty
                                <x-ui.empty-state title="No visible comments yet" description="Requester-visible communication from staff will appear here." />
                            @endforelse
                        </div>

                        @if ($canAddComment)
                            <form method="POST" action="{{ route('client-requests.comments.store', ['projectRequest' => $projectRequest]) }}" class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                                @csrf
                                <div>
                                    <x-input-label for="comment_body" value="Add an update" />
                                    <x-textarea-input id="comment_body" name="body" rows="5" class="mt-2 block w-full" required>{{ old('body') }}</x-textarea-input>
                                    <p class="mt-2 text-xs text-slate-500">Your comment will remain visible to you and authorized staff reviewers.</p>
                                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3">
                                    <button type="submit" class="usn-btn-primary">Post Comment</button>
                                </div>
                            </form>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
