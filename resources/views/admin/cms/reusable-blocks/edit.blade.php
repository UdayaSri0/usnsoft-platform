<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Edit Reusable Block"
            description="{{ $block->name }} · {{ $block->workflow_state->value }}"
            eyebrow="CMS"
        >
            <x-slot name="actions">
                <a href="{{ route('admin.cms.reusable-blocks.index') }}" class="usn-btn-secondary">Back</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
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
