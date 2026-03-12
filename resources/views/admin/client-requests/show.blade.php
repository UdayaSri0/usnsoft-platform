<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$projectRequest->project_title"
            :description="$projectRequest->project_summary"
            eyebrow="Client Requests"
        >
            <x-slot name="actions">
                <x-client-request-status-badge :status="$projectRequest->currentStatus" />
                <a href="{{ route('admin.client-requests.index') }}" class="usn-btn-secondary">Back to Queue</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
                <div class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Requester profile</h2>
                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Requester</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->requester_name }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Account</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->requester?->email ?? 'Deleted account' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Company</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->company_name ?: '-' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Request Type</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->project_type?->label() ?? '-' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Budget</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->budget !== null ? '$'.number_format((float) $projectRequest->budget, 2) : '-' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Deadline</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->deadline?->format('M j, Y') ?: '-' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Contact Email</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->contact_email }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Contact Phone</dt>
                                <dd class="mt-2 text-sm text-slate-800">{{ $projectRequest->contact_phone ?: '-' }}</dd>
                            </div>
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
                        <h2 class="font-display text-xl font-semibold text-slate-950">Workflow controls</h2>
                        <p class="mt-2 text-sm text-slate-600">Status transitions append immutable history entries and can trigger requester notifications when visible.</p>

                        <form method="POST" action="{{ route('admin.client-requests.status.transition', ['projectRequest' => $projectRequest]) }}" class="mt-5 grid gap-4 lg:grid-cols-[1fr_1.2fr_auto]">
                            @csrf
                            <div>
                                <x-input-label for="status_id" value="Next Status" />
                                <x-select-input id="status_id" name="status_id" class="mt-2 block w-full" required>
                                    @foreach ($availableStatuses as $status)
                                        <option value="{{ $status->getKey() }}" @selected(old('status_id', $projectRequest->current_status_id) == $status->getKey())>{{ $status->name }}</option>
                                    @endforeach
                                </x-select-input>
                                <x-input-error :messages="$errors->get('status_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="change_note" value="Status Note" />
                                <x-textarea-input id="change_note" name="change_note" rows="3" class="mt-2 block w-full">{{ old('change_note') }}</x-textarea-input>
                                <x-input-error :messages="$errors->get('change_note')" class="mt-2" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="usn-btn-primary w-full">Apply Status</button>
                            </div>
                        </form>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Protected attachments</h2>

                        <div class="mt-5 space-y-3">
                            @forelse ($projectRequest->attachments as $attachment)
                                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-muted">{{ $attachment->category->label() }}</span>
                                            <span class="usn-badge-muted">{{ $attachment->visible_to_requester ? 'Requester visible' : 'Internal only' }}</span>
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-900">{{ $attachment->original_name }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Number::fileSize($attachment->size_bytes) }} · {{ $attachment->mime_type ?: 'Unknown type' }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-500">Malware status: {{ $attachment->malware_scan_status->label() }}</p>
                                    </div>
                                    <a href="{{ route('admin.client-requests.attachments.show', ['attachment' => $attachment]) }}" class="usn-btn-secondary">Download</a>
                                </div>
                            @empty
                                <x-ui.empty-state title="No attachments uploaded" description="Supporting files will appear here when the requester provides them." />
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Internal-only discussion</h2>
                        <p class="mt-2 text-sm text-slate-700">Nothing in this section is visible to the requester unless a comment is intentionally moved to requester-visible.</p>

                        <form method="POST" action="{{ route('admin.client-requests.comments.internal.store', ['projectRequest' => $projectRequest]) }}" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="internal_comment_body" value="Add Internal Comment" />
                                <x-textarea-input id="internal_comment_body" name="body" rows="4" class="mt-2 block w-full" required>{{ old('body') }}</x-textarea-input>
                                <x-input-error :messages="$errors->get('body')" class="mt-2" />
                            </div>
                            <button type="submit" class="usn-btn-secondary">Post Internal Comment</button>
                        </form>

                        <div class="mt-6 space-y-4">
                            @forelse ($internalComments as $comment)
                                <article class="rounded-2xl border border-amber-200 bg-white p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-warning">Internal</span>
                                            <p class="font-semibold text-slate-900">{{ $comment->author?->name ?? 'System' }}</p>
                                        </div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $comment->created_at?->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->body }}</p>
                                    @can('changeVisibility', $comment)
                                        <form method="POST" action="{{ route('admin.client-requests.comments.visibility.update', ['comment' => $comment]) }}" class="mt-4">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="visibility_type" value="{{ \App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility::RequesterVisible->value }}">
                                            <button type="submit" class="text-sm font-semibold text-amber-700 hover:text-amber-900">Make requester visible</button>
                                        </form>
                                    @endcan
                                </article>
                            @empty
                                <x-ui.empty-state title="No internal comments yet" description="Operational notes, qualification comments, and privileged discussion can stay here." class="bg-white" />
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-sky-200 bg-sky-50 p-6">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Requester-visible updates</h2>
                        <p class="mt-2 text-sm text-slate-700">This section is visible to the requester. Use it intentionally for status notes and customer-facing communication.</p>

                        <form method="POST" action="{{ route('admin.client-requests.comments.requester-visible.store', ['projectRequest' => $projectRequest]) }}" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="requester_comment_body" value="Add Requester-visible Comment" />
                                <x-textarea-input id="requester_comment_body" name="body" rows="4" class="mt-2 block w-full" required>{{ old('body') }}</x-textarea-input>
                            </div>
                            <button type="submit" class="usn-btn-primary">Post Visible Update</button>
                        </form>

                        <div class="mt-6 space-y-4">
                            @forelse ($requesterVisibleComments as $comment)
                                <article class="rounded-2xl border border-sky-200 bg-white p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-info">Requester Visible</span>
                                            <p class="font-semibold text-slate-900">{{ $comment->author?->name ?? 'System' }}</p>
                                        </div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $comment->created_at?->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->body }}</p>
                                    @can('changeVisibility', $comment)
                                        <form method="POST" action="{{ route('admin.client-requests.comments.visibility.update', ['comment' => $comment]) }}" class="mt-4">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="visibility_type" value="{{ \App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility::Internal->value }}">
                                            <button type="submit" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Move back to internal</button>
                                        </form>
                                    @endcan
                                </article>
                            @empty
                                <x-ui.empty-state title="No requester-visible comments yet" description="Visible updates will appear here and on the requester account page." class="bg-white" />
                            @endforelse
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Status history</h2>

                        <div class="mt-5 space-y-4">
                            @forelse ($projectRequest->statusHistories as $history)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-muted">{{ $history->visibility === \App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility::RequesterVisible->value ? 'Requester visible' : 'Internal' }}</span>
                                            <p class="font-semibold text-slate-900">{{ $history->metadata['from_status_name'] ?? $history->from_state ?? 'Start' }} → {{ $history->metadata['to_status_name'] ?? $history->to_state }}</p>
                                        </div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $history->changed_at?->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-600">Changed by {{ $history->changer?->name ?? 'System' }}</p>
                                    @if ($history->reason)
                                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $history->reason }}</p>
                                    @endif
                                </article>
                            @empty
                                <x-ui.empty-state title="No status history recorded" description="Status transitions will append immutable history entries here." />
                            @endforelse
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Requester timeline preview</h2>
                        <p class="mt-2 text-sm text-slate-600">This is the visible timeline the requester can see from their account area.</p>

                        <div class="mt-5 space-y-4">
                            @forelse ($requesterTimeline as $item)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="usn-badge-muted">{{ \Illuminate\Support\Str::headline($item['kind']) }}</span>
                                            <p class="font-semibold text-slate-900">{{ $item['title'] }}</p>
                                        </div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ optional($item['occurred_at'])->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $item['body'] }}</p>
                                    @if (! empty($item['note']))
                                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $item['note'] }}</p>
                                    @endif
                                </article>
                            @empty
                                <x-ui.empty-state title="Nothing visible yet" description="Requester-visible workflow notes will appear here as soon as they exist." />
                            @endforelse
                        </div>
                    </section>

                    @if ($auditLogs->isNotEmpty())
                        <section class="usn-card">
                            <h2 class="font-display text-xl font-semibold text-slate-950">Audit trail</h2>

                            <div class="mt-5 space-y-4">
                                @foreach ($auditLogs as $auditLog)
                                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $auditLog->action }}</p>
                                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $auditLog->event_type }}</p>
                                            </div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $auditLog->occurred_at?->format('M j, Y g:i A') }}</p>
                                        </div>
                                        <p class="mt-3 text-sm text-slate-600">Actor: {{ $auditLog->actor?->name ?? 'System' }}</p>
                                        @if (! empty($auditLog->metadata))
                                            <pre class="mt-3 overflow-x-auto rounded-2xl bg-slate-950 p-3 text-xs text-slate-100">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
