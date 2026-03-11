<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-semibold text-slate-900">Block Definitions</h1>
            <p class="mt-1 text-sm text-slate-500">SuperAdmin metadata controls for safe block types.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="space-y-4">
                @foreach ($definitions as $definition)
                    <form method="POST" action="{{ route('admin.cms.block-definitions.update', $definition) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                                <input type="text" name="name" value="{{ old('name', $definition->name) }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
                                <input type="text" name="category" value="{{ old('category', $definition->category) }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Editor mode</label>
                                <select name="editor_mode" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                    @foreach ($editorModes as $mode)
                                        <option value="{{ $mode->value }}" @selected($definition->editor_mode === $mode)>{{ $mode->value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2 lg:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Description</label>
                                <textarea name="description" rows="2" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">{{ old('description', $definition->description) }}</textarea>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($definition->is_active) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                    Active
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                    <input type="hidden" name="is_reusable_allowed" value="0">
                                    <input type="checkbox" name="is_reusable_allowed" value="1" @checked($definition->is_reusable_allowed) class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                    Reusable
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort order</label>
                                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $definition->sort_order) }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
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
