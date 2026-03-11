<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Block Definitions"
            description="SuperAdmin metadata controls for safe block types."
            eyebrow="System"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <div class="space-y-4">
                @foreach ($definitions as $definition)
                    <form method="POST" action="{{ route('admin.cms.block-definitions.update', $definition) }}" class="usn-card">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="usn-label">Name</label>
                                <input type="text" name="name" value="{{ old('name', $definition->name) }}" class="usn-input mt-2 block w-full">
                            </div>

                            <div>
                                <label class="usn-label">Category</label>
                                <input type="text" name="category" value="{{ old('category', $definition->category) }}" class="usn-input mt-2 block w-full">
                            </div>

                            <div>
                                <label class="usn-label">Editor mode</label>
                                <select name="editor_mode" class="usn-select mt-2 block w-full">
                                    @foreach ($editorModes as $mode)
                                        <option value="{{ $mode->value }}" @selected($definition->editor_mode === $mode)>{{ $mode->value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2 lg:col-span-3">
                                <label class="usn-label">Description</label>
                                <textarea name="description" rows="2" class="usn-textarea mt-2 block w-full">{{ old('description', $definition->description) }}</textarea>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($definition->is_active) class="usn-checkbox">
                                    Active
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                    <input type="hidden" name="is_reusable_allowed" value="0">
                                    <input type="checkbox" name="is_reusable_allowed" value="1" @checked($definition->is_reusable_allowed) class="usn-checkbox">
                                    Reusable
                                </label>
                            </div>

                            <div>
                                <label class="usn-label">Sort order</label>
                                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $definition->sort_order) }}" class="usn-input mt-2 block w-full">
                            </div>

                            <div class="flex items-end justify-end">
                                <x-primary-button type="submit">Save</x-primary-button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
