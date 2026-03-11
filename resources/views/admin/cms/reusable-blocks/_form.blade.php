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

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $block->name ?? '')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-1 block w-full" :value="old('slug', $block->slug ?? '')" required />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="block_definition_id" value="Block Definition" />
                <select id="block_definition_id" name="block_definition_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                    @foreach ($definitions as $definition)
                        <option value="{{ $definition->id }}" @selected((int) $selectedDefinitionId === (int) $definition->id)>{{ $definition->name }} ({{ $definition->key }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('block_definition_id')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="data_json" value="Data (JSON)" />
                <textarea id="data_json" name="data_json" rows="10" class="mt-1 block w-full rounded-xl border-slate-300 font-mono text-xs focus:border-sky-500 focus:ring-sky-500">{{ $jsonPayload }}</textarea>
                <p class="mt-1 text-xs text-slate-500">Payload is sanitized and validated against the selected block schema.</p>
            </div>

            <div class="md:col-span-2">
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">{{ old('notes', $block->notes ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.cms.reusable-blocks.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
