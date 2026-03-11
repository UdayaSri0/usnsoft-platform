<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-2xl font-semibold text-slate-900">CMS Pages</h1>
                <p class="mt-1 text-sm text-slate-500">Draft, review, approval, and publish management.</p>
            </div>
            @can('create', \App\Modules\Pages\Models\Page::class)
                <a href="{{ route('admin.cms.pages.create') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow">New Page</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
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
                                        <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">
                                            v{{ $page->currentDraftVersion->version_number }} · {{ $page->currentDraftVersion->workflow_state->value }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500">No draft</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($page->currentPublishedVersion)
                                        <span class="rounded-lg bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">v{{ $page->currentPublishedVersion->version_number }}</span>
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

            {{ $pages->links() }}
        </div>
    </div>
</x-app-layout>
