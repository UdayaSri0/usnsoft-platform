<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Job Listing" description="Draft a public career opportunity." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide">
            @include('admin.careers._form', ['job' => null, 'action' => route('admin.careers.store'), 'method' => 'POST', 'submitLabel' => 'Create Draft'])
        </div>
    </div>
</x-app-layout>
