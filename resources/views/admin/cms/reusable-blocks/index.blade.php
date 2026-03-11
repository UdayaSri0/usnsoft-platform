<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Reusable Blocks"
            description="Shared approved content building blocks for repeated use across pages."
            eyebrow="CMS"
        >
            <x-slot name="actions">
                <a href="{{ route('admin.cms.reusable-blocks.create') }}" class="usn-btn-primary">New Reusable Block</a>
            </x-slot>
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
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Definition</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Updated</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($blocks as $block)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $block->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $block->slug }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $block->blockDefinition?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="usn-badge-muted">{{ $block->workflow_state->value }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $block->updated_at }}</td>
                                <td class="px-4 py-3"><a href="{{ route('admin.cms.reusable-blocks.edit', $block) }}" class="text-sm font-semibold text-sky-700">Edit</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No reusable blocks yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

            {{ $blocks->links() }}
        </div>
    </div>
</x-app-layout>
