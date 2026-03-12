<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Client Requests"
            description="Operational intake queue for project ideas, inquiries, quotations, and meeting requests."
            eyebrow="Client Requests"
        >
            <x-slot name="actions">
                @if (auth()->user()->hasPermission('requests.statuses.manage'))
                    <a href="{{ route('admin.client-requests.statuses.index') }}" class="usn-btn-secondary">Statuses</a>
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <form method="GET" action="{{ route('admin.client-requests.index') }}" class="usn-toolbar">
                <div class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <x-text-input name="q" :value="$filters['q']" placeholder="Search title, requester, email, company" />

                    <x-select-input name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->code }}" @selected($filters['status'] === $status->code)>{{ $status->name }}</option>
                        @endforeach
                    </x-select-input>

                    <x-select-input name="type">
                        <option value="">All request types</option>
                        @foreach ($projectTypes as $projectType)
                            <option value="{{ $projectType->value }}" @selected($filters['type'] === $projectType->value)>{{ $projectType->label() }}</option>
                        @endforeach
                    </x-select-input>

                    <x-text-input name="submitted_from" type="date" :value="$filters['submittedFrom']" />
                    <x-text-input name="submitted_to" type="date" :value="$filters['submittedTo']" />
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="usn-btn-primary">Filter</button>
                    <a href="{{ route('admin.client-requests.index') }}" class="usn-btn-secondary">Reset</a>
                </div>
            </form>

            <div class="usn-table-shell">
                <div class="usn-table-scroll">
                    <table class="usn-table">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Request</th>
                                <th class="px-4 py-3">Requester</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($projectRequests as $projectRequest)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">{{ $projectRequest->project_title }}</p>
                                        <p class="text-xs text-slate-500">{{ $projectRequest->project_summary }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $projectRequest->requester_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $projectRequest->contact_email }}</p>
                                        @if ($projectRequest->company_name)
                                            <p class="text-xs text-slate-500">{{ $projectRequest->company_name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $projectRequest->project_type?->label() ?? 'Request' }}</td>
                                    <td class="px-4 py-3">
                                        <x-client-request-status-badge :status="$projectRequest->currentStatus" />
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $projectRequest->submitted_at?->format('M j, Y g:i A') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.client-requests.show', ['projectRequest' => $projectRequest]) }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Open workflow</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No client requests match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $projectRequests->links() }}
        </div>
    </div>
</x-app-layout>
