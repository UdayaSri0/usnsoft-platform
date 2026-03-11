<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Product Tags"
            description="Use tags for product discovery, related grouping, and public filtering without overloading the category structure."
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
                <h2 class="font-display text-xl font-semibold text-slate-950">New tag</h2>
                <form method="POST" action="{{ route('admin.products.tags.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="tag_name" value="Name" />
                        <x-text-input id="tag_name" name="name" class="mt-2 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="tag_slug" value="Slug (optional)" />
                        <x-text-input id="tag_slug" name="slug" class="mt-2 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="tag_description" value="Description" />
                        <x-textarea-input id="tag_description" name="description" rows="4" class="mt-2 block w-full"></x-textarea-input>
                    </div>
                    <button type="submit" class="usn-btn-primary">Create tag</button>
                </form>
            </section>

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Existing tags</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($tags as $tag)
                        <form method="POST" action="{{ route('admin.products.tags.update', ['tag' => $tag->getKey()]) }}" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_auto]">
                                <div>
                                    <x-input-label :for="'tag-name-'.$tag->getKey()" value="Name" />
                                    <x-text-input :id="'tag-name-'.$tag->getKey()" name="name" class="mt-2 block w-full" :value="$tag->name" />
                                </div>
                                <div>
                                    <x-input-label :for="'tag-slug-'.$tag->getKey()" value="Slug" />
                                    <x-text-input :id="'tag-slug-'.$tag->getKey()" name="slug" class="mt-2 block w-full" :value="$tag->slug" />
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="usn-btn-secondary">Save</button>
                                </div>
                                <div class="md:col-span-2 xl:col-span-3">
                                    <x-input-label :for="'tag-description-'.$tag->getKey()" value="Description" />
                                    <x-textarea-input :id="'tag-description-'.$tag->getKey()" name="description" rows="3" class="mt-2 block w-full">{{ $tag->description }}</x-textarea-input>
                                    <p class="mt-2 text-xs text-slate-500">{{ $tag->versions_count }} version{{ $tag->versions_count === 1 ? '' : 's' }} linked</p>
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
