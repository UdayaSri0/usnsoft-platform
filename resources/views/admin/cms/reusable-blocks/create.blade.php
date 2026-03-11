<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Create Reusable Block"
            description="Create shared block content for repeated use across pages."
            eyebrow="CMS"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container">
            @include('admin.cms.reusable-blocks._form', [
                'action' => route('admin.cms.reusable-blocks.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Block',
                'block' => null,
            ])
        </div>
    </div>
</x-app-layout>
