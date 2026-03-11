<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-2xl font-semibold text-slate-900">Reusable Blocks</h1>
                <p class="mt-1 text-sm text-slate-500">Shared approved content building blocks.</p>
            </div>
            <a href="{{ route('admin.cms.reusable-blocks.create') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow">New Reusable Block</a>
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
                                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $block->workflow_state->value }}</span>
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

            {{ $blocks->links() }}
        </div>
    </div>
</x-app-layout>
