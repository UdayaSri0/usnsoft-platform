<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-2xl font-semibold text-slate-900">Edit Reusable Block</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $block->name }} · {{ $block->workflow_state->value }}</p>
            </div>
            <a href="{{ route('admin.cms.reusable-blocks.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif

            @include('admin.cms.reusable-blocks._form', [
                'action' => route('admin.cms.reusable-blocks.update', $block),
                'method' => 'PUT',
                'submitLabel' => 'Save Block',
                'block' => $block,
            ])
        </div>
    </div>
</x-app-layout>
