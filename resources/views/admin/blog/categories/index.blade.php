<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Blog Categories" description="Organize blog and news content." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">New category</h2>
                <form method="POST" action="{{ route('admin.blog.categories.store') }}" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    @csrf
                    <x-text-input name="name" :value="old('name')" placeholder="Name" />
                    <x-text-input name="slug" :value="old('slug')" placeholder="Slug" />
                    <x-text-input name="description" :value="old('description')" placeholder="Description" />
                    <x-text-input type="number" name="sort_order" :value="old('sort_order', 0)" placeholder="Sort order" />
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked(true) class="usn-checkbox">
                        Active
                    </label>
                    <div class="md:col-span-2 xl:col-span-5">
                        <x-primary-button>Create Category</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="usn-card space-y-4">
                @foreach ($categories as $category)
                    <form method="POST" action="{{ route('admin.blog.categories.update', ['category' => $category->getKey()]) }}" class="grid gap-4 border-b border-slate-200 pb-4 last:border-0 last:pb-0 md:grid-cols-2 xl:grid-cols-6">
                        @csrf
                        @method('PUT')
                        <x-text-input name="name" :value="$category->name" />
                        <x-text-input name="slug" :value="$category->slug" />
                        <x-text-input name="description" :value="$category->description" />
                        <x-text-input type="number" name="sort_order" :value="$category->sort_order" />
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="usn-checkbox">
                                Active
                            </label>
                            <span>{{ $category->posts_count }} posts</span>
                        </div>
                        <div class="flex items-center justify-end">
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
