<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Create CMS Page"
            description="Create a draft page version using safe structured blocks."
            eyebrow="CMS"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide">
            @if ($errors->any())
                <x-ui.alert tone="danger" title="Please fix the highlighted validation issues before continuing." class="mb-4" />
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
