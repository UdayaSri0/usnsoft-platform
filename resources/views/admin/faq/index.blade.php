<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="FAQ" description="Searchable answers with publishing workflow." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <form method="GET" class="grid flex-1 gap-3 md:grid-cols-4">
                        <x-text-input name="q" :value="$filters['q']" placeholder="Search FAQs" />
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
                        <div class="flex gap-2">
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.faq.categories.index') }}" class="usn-btn-secondary">Categories</a>
                        <a href="{{ route('admin.faq.create') }}" class="usn-btn-primary">New FAQ</a>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="pb-3">Question</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3">Linked Product</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($faqs as $faq)
                                <tr>
                                    <td class="py-4">
                                        <p class="font-semibold text-slate-900">{{ $faq->question }}</p>
                                    </td>
                                    <td class="py-4">{{ $faq->category?->name ?? 'General' }}</td>
                                    <td class="py-4">{{ $faq->linkedProduct?->name_current ?? 'None' }}</td>
                                    <td class="py-4">
                                        <span class="usn-badge-warning">{{ $faq->workflow_state->value }}</span>
                                        <span class="usn-badge-info">Approval: {{ $faq->approval_state->value }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('admin.faq.edit', ['faq' => $faq->getKey()]) }}" class="usn-link">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6">
                                        <x-ui.empty-state title="No FAQs yet" description="Create the first FAQ draft to seed the public knowledge base." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $faqs->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
