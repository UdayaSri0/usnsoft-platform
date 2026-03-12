<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$resourceLabel" description="Managed inside the approval-driven showcase content area." eyebrow="Showcase" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <form method="GET" class="grid flex-1 gap-3 md:grid-cols-4">
                        <x-text-input name="q" :value="$filters['q']" placeholder="Search {{ strtolower($resourceLabel) }}" />
                        <x-select-input name="status">
                            <option value="">All statuses</option>
                            @foreach ($workflowStates as $state)
                                <option value="{{ $state->value }}" @selected($filters['status'] === $state->value)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                            @endforeach
                        </x-select-input>
                        <x-select-input name="featured">
                            <option value="">Featured?</option>
                            <option value="1" @selected($filters['featured'] === '1')>Featured</option>
                            <option value="0" @selected($filters['featured'] === '0')>Standard</option>
                        </x-select-input>
                        <div class="flex justify-end">
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <a href="{{ route($routeBase.'.create') }}" class="usn-btn-primary">New Entry</a>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="pb-3">Entry</th>
                                <th class="pb-3">Sort</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Published</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="py-4">
                                        <p class="font-semibold text-slate-900">{{ $item->title ?? $item->name ?? $item->full_name ?? $item->client_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            @if (data_get($item, 'slug'))
                                                {{ $item->slug ?? 'No slug' }}
                                            @elseif (isset($item->role_title))
                                                {{ $item->role_title }}
                                            @elseif (isset($item->company_name))
                                                {{ $item->company_name }}
                                            @endif
                                        </p>
                                    </td>
                                    <td class="py-4">{{ $item->sort_order }}</td>
                                    <td class="py-4">
                                        <span class="usn-badge-warning">{{ $item->workflow_state->value }}</span>
                                        <span class="usn-badge-info">Approval: {{ $item->approval_state->value }}</span>
                                    </td>
                                    <td class="py-4">{{ $item->published_at?->format('M j, Y') ?? 'Not published' }}</td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route($routeBase.'.edit', ['item' => $item->getKey()]) }}" class="usn-link">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6">
                                        <x-ui.empty-state title="No entries yet" description="Create the first draft to populate this showcase module." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $items->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
