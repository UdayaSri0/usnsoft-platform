<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New FAQ" description="Draft a structured answer for approval." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide">
            @include('admin.faq._form', ['faq' => null, 'action' => route('admin.faq.store'), 'method' => 'POST', 'submitLabel' => 'Create Draft'])
        </div>
    </div>
</x-app-layout>
