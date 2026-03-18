<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Blog & News" description="Editorial drafts, approvals, and publishing." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <form method="GET" class="grid flex-1 gap-3 md:grid-cols-7">
                        <x-text-input name="q" :value="$filters['q']" placeholder="Search blog posts" />

                        <x-select-input name="status">
                            <option value="">All statuses</option>
                            @foreach ($workflowStates as $state)
                                <option value="{{ $state->value }}" @selected($filters['status'] === $state->value)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                            @endforeach
                        </x-select-input>

                        <x-select-input name="category">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </x-select-input>

                        <x-select-input name="tag">
                            <option value="">All tags</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->slug }}" @selected($filters['tag'] === $tag->slug)>{{ $tag->name }}</option>
                            @endforeach
                        </x-select-input>

                        <x-select-input name="author">
                            <option value="">All authors</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->getKey() }}" @selected((string) $filters['author'] === (string) $author->getKey())>{{ $author->name }}</option>
                            @endforeach
                        </x-select-input>

                        <x-text-input name="date_from" type="date" :value="$filters['dateFrom']" />
                        <x-text-input name="date_to" type="date" :value="$filters['dateTo']" />

                        <div class="flex gap-2">
                            <x-select-input name="featured">
                                <option value="">Featured?</option>
                                <option value="1" @selected($filters['featured'] === '1')>Featured</option>
                                <option value="0" @selected($filters['featured'] === '0')>Standard</option>
                            </x-select-input>
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.blog.categories.index') }}" class="usn-btn-secondary">Categories</a>
                        <a href="{{ route('admin.blog.tags.index') }}" class="usn-btn-secondary">Tags</a>
                        <a href="{{ route('admin.blog.create') }}" class="usn-btn-primary">New Post</a>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="pb-3">Post</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3">Author</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Comments</th>
                                <th class="pb-3">Published</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($posts as $post)
                                <tr>
                                    <td class="py-4">
                                        <p class="font-semibold text-slate-900">{{ $post->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $post->slug }}</p>
                                    </td>
                                    <td class="py-4">{{ $post->category?->name ?? 'Uncategorized' }}</td>
                                    <td class="py-4">{{ $post->author?->name ?? 'Editorial' }}</td>
                                    <td class="py-4">
                                        <span class="usn-badge-warning">{{ $post->workflow_state->value }}</span>
                                        <span class="usn-badge-info">Approval: {{ $post->approval_state->value }}</span>
                                    </td>
                                    <td class="py-4">
                                        <span class="usn-badge-success">{{ $post->approved_comments_count }} approved</span>
                                        <span class="usn-badge-warning">{{ $post->pending_comments_count }} pending</span>
                                    </td>
                                    <td class="py-4">{{ $post->published_at?->format('M j, Y g:i A') ?? 'Not published' }}</td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('admin.blog.edit', ['post' => $post->getKey()]) }}" class="usn-link">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6">
                                        <x-ui.empty-state title="No blog posts yet" description="Create the first draft to start the editorial workflow." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $posts->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
