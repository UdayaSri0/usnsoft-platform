<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Create Product"
            description="Start a new product draft with structured content, downloads, visibility rules, and SEO metadata."
            eyebrow="Product Platform"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @include('admin.products._form', [
                'action' => route('admin.products.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Draft',
                'createMode' => true,
            ])
        </div>
    </div>
</x-app-layout>
