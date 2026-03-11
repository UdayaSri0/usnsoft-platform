@php
    $selectedDefinitionId = old('block_definition_id', $block->block_definition_id ?? null);
    $jsonPayload = old('data_json');

    if (! is_string($jsonPayload)) {
        $jsonPayload = json_encode($block->data_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <section class="usn-card">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="mt-2 block w-full" :value="old('name', $block->name ?? '')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-2 block w-full" :value="old('slug', $block->slug ?? '')" required />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="block_definition_id" value="Block Definition" />
                <x-select-input id="block_definition_id" name="block_definition_id" class="mt-2 block w-full">
                    @foreach ($definitions as $definition)
                        <option value="{{ $definition->id }}" @selected((int) $selectedDefinitionId === (int) $definition->id)>{{ $definition->name }} ({{ $definition->key }})</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('block_definition_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="data_json" value="Data (JSON)" />
                <textarea id="data_json" name="data_json" rows="10" class="usn-textarea mt-2 block w-full font-mono text-xs">{{ $jsonPayload }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Payload is sanitized and validated against the selected block schema.</p>
            </div>

            <div class="md:col-span-2">
                <x-input-label for="notes" value="Notes" />
                <x-textarea-input id="notes" name="notes" rows="3" class="mt-2 block w-full">{{ old('notes', $block->notes ?? '') }}</x-textarea-input>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.cms.reusable-blocks.index') }}" class="usn-btn-secondary">Cancel</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
