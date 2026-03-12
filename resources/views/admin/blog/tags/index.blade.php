<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Blog Tags" description="Manage editorial tag filters." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">New tag</h2>
                <form method="POST" action="{{ route('admin.blog.tags.store') }}" class="mt-4 grid gap-4 md:grid-cols-3">
                    @csrf
                    <x-text-input name="name" :value="old('name')" placeholder="Name" />
                    <x-text-input name="slug" :value="old('slug')" placeholder="Slug" />
                    <div class="flex items-center justify-end">
                        <x-primary-button>Create Tag</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="usn-card space-y-4">
                @foreach ($tags as $tag)
                    <form method="POST" action="{{ route('admin.blog.tags.update', ['tag' => $tag->getKey()]) }}" class="grid gap-4 border-b border-slate-200 pb-4 last:border-0 last:pb-0 md:grid-cols-3">
                        @csrf
                        @method('PUT')
                        <x-text-input name="name" :value="$tag->name" />
                        <x-text-input name="slug" :value="$tag->slug" />
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-slate-500">{{ $tag->posts_count }} posts</span>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
