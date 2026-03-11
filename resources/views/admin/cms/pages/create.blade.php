<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-semibold text-slate-900">Create CMS Page</h1>
            <p class="mt-1 text-sm text-slate-500">Create a draft page version with safe structured blocks.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    Please fix the highlighted validation issues before continuing.
                </div>
            @endif

            @include('admin.cms.pages._form', [
                'action' => route('admin.cms.pages.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Draft',
                'createMode' => true,
                'draft' => null,
            ])
        </div>
    </div>
</x-app-layout>
