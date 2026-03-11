<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-semibold text-slate-900">Create Reusable Block</h1>
            <p class="mt-1 text-sm text-slate-500">Create shared block content for repeated use across pages.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @include('admin.cms.reusable-blocks._form', [
                'action' => route('admin.cms.reusable-blocks.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Block',
                'block' => null,
            ])
        </div>
    </div>
</x-app-layout>
