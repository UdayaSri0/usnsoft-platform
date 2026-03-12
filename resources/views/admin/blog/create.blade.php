<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Blog Post" description="Structured editorial draft with approval workflow." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide">
            @include('admin.blog._form', [
                'action' => route('admin.blog.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Draft',
                'createMode' => true,
                'post' => null,
            ])
        </div>
    </div>
</x-app-layout>
