<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="CMS Pages"
            description="Draft, review, approval, and publish management for the public website."
            eyebrow="CMS"
        >
            @can('create', \App\Modules\Pages\Models\Page::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.cms.pages.create') }}" class="usn-btn-primary">New Page</a>
                </x-slot>
            @endcan
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <div class="usn-table-shell">
                <div class="usn-table-scroll">
                    <table class="usn-table">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Page</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Path</th>
                            <th class="px-4 py-3">Draft</th>
                            <th class="px-4 py-3">Published</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pages as $page)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $page->title_current }}</p>
                                    <p class="text-xs text-slate-500">
                                        @if ($page->is_home) Home page @endif
                                        @if ($page->is_system_page && ! $page->is_home)
                                            System page
                                        @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $page->page_type?->value ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $page->path_current }}</td>
                                <td class="px-4 py-3">
                                    @if ($page->currentDraftVersion)
                                        <span class="usn-badge-warning">
                                            v{{ $page->currentDraftVersion->version_number }} · {{ $page->currentDraftVersion->workflow_state->value }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500">No draft</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($page->currentPublishedVersion)
                                        <span class="usn-badge-success">v{{ $page->currentPublishedVersion->version_number }}</span>
                                    @else
                                        <span class="text-xs text-slate-500">Not published</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.cms.pages.edit', $page) }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Open Editor</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No CMS pages created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

            {{ $pages->links() }}
        </div>
    </div>
</x-app-layout>
