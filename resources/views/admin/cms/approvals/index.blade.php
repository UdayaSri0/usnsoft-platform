<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Approval Queue"
            description="Review pending content versions and approve, reject, or publish."
            eyebrow="Workflow"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            <div class="usn-table-shell">
                <div class="usn-table-scroll">
                    <table class="usn-table">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Page</th>
                            <th class="px-4 py-3">Version</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Submitted At</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($versions as $version)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $version->page->title_current }}</p>
                                    <p class="text-xs text-slate-500">{{ $version->path }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">v{{ $version->version_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="usn-badge-warning">{{ $version->workflow_state->value }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $version->submitted_at ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.cms.pages.edit', $version->page) }}" class="text-xs font-semibold text-sky-700">Open</a>
                                        <a href="{{ route('cms.preview.show', $version) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-indigo-700">Preview</a>
                                        @if ($version->workflow_state === \App\Enums\ContentWorkflowState::InReview)
                                            <form method="POST" action="{{ route('admin.cms.versions.approve', $version) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-2 py-1 text-xs font-semibold text-white">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.cms.versions.reject', $version) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-semibold text-white">Reject</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No pending items in the queue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

            {{ $versions->links() }}
        </div>
    </div>
</x-app-layout>
