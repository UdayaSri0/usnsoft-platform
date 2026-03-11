<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-2xl font-semibold text-slate-900">Approval Queue</h1>
                <p class="mt-1 text-sm text-slate-500">Review pending content versions and approve, reject, or publish.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
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
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">{{ $version->workflow_state->value }}</span>
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

            {{ $versions->links() }}
        </div>
    </div>
</x-app-layout>
