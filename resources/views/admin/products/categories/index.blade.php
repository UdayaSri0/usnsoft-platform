<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Product Categories"
            description="Keep product classification clean for public filtering and internal organization."
            eyebrow="Product Platform"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            @if (session('status'))
                <div class="xl:col-span-2">
                    <x-ui.alert tone="success" :title="session('status')" />
                </div>
            @endif

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">New category</h2>
                <form method="POST" action="{{ route('admin.products.categories.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="category_name" value="Name" />
                        <x-text-input id="category_name" name="name" class="mt-2 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="category_slug" value="Slug (optional)" />
                        <x-text-input id="category_slug" name="slug" class="mt-2 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="category_description" value="Description" />
                        <x-textarea-input id="category_description" name="description" rows="4" class="mt-2 block w-full"></x-textarea-input>
                    </div>
                    <div>
                        <x-input-label for="category_sort_order" value="Sort order" />
                        <x-text-input id="category_sort_order" name="sort_order" type="number" min="0" class="mt-2 block w-full" value="0" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="usn-checkbox">
                        Active
                    </label>
                    <button type="submit" class="usn-btn-primary">Create category</button>
                </form>
            </section>

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Existing categories</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($categories as $category)
                        <form method="POST" action="{{ route('admin.products.categories.update', ['category' => $category->getKey()]) }}" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_120px_auto]">
                                <div>
                                    <x-input-label :for="'category-name-'.$category->getKey()" value="Name" />
                                    <x-text-input :id="'category-name-'.$category->getKey()" name="name" class="mt-2 block w-full" :value="$category->name" />
                                </div>
                                <div>
                                    <x-input-label :for="'category-slug-'.$category->getKey()" value="Slug" />
                                    <x-text-input :id="'category-slug-'.$category->getKey()" name="slug" class="mt-2 block w-full" :value="$category->slug" />
                                </div>
                                <div>
                                    <x-input-label :for="'category-sort-'.$category->getKey()" value="Sort" />
                                    <x-text-input :id="'category-sort-'.$category->getKey()" name="sort_order" type="number" min="0" class="mt-2 block w-full" :value="$category->sort_order" />
                                </div>
                                <div class="flex items-end justify-between gap-3">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="usn-checkbox">
                                        Active
                                    </label>
                                    <button type="submit" class="usn-btn-secondary">Save</button>
                                </div>
                                <div class="md:col-span-2 xl:col-span-4">
                                    <x-input-label :for="'category-description-'.$category->getKey()" value="Description" />
                                    <x-textarea-input :id="'category-description-'.$category->getKey()" name="description" rows="3" class="mt-2 block w-full">{{ $category->description }}</x-textarea-input>
                                    <p class="mt-2 text-xs text-slate-500">{{ $category->versions_count }} version{{ $category->versions_count === 1 ? '' : 's' }} linked</p>
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
