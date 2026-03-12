<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="My Client Requests"
            description="Track current status, visible updates, and protected request files from your account workspace."
            eyebrow="Client Requests"
        >
            <x-slot name="actions">
                @can('create', \App\Modules\ClientRequests\Models\ProjectRequest::class)
                    <a href="{{ route('client-requests.create') }}" class="usn-btn-primary">New Request</a>
                @endcan
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status') === 'project-request-submitted')
                <x-ui.alert tone="success" title="Request submitted">
                    Your request has been recorded and routed into the internal review workflow.
                </x-ui.alert>
            @endif

            @if (! auth()->user()->hasVerifiedEmail())
                <x-ui.alert tone="warning" title="Email verification required for new submissions">
                    You can review your existing requests, but new submissions and protected request actions require a verified email address.
                </x-ui.alert>
            @endif

            <form method="GET" action="{{ route('client-requests.index') }}" class="usn-toolbar">
                <div class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
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
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="usn-btn-primary">Filter</button>
                    <a href="{{ route('client-requests.index') }}" class="usn-btn-secondary">Reset</a>
                </div>
            </form>

            <div class="space-y-4">
                @forelse ($projectRequests as $projectRequest)
                    <article class="usn-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-client-request-status-badge :status="$projectRequest->currentStatus" />
                                    <span class="usn-badge-muted">{{ $projectRequest->project_type?->label() ?? 'Request' }}</span>
                                </div>

                                <h2 class="mt-4 font-display text-2xl font-semibold text-slate-950">{{ $projectRequest->project_title }}</h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $projectRequest->project_summary }}</p>

                                <div class="mt-4 flex flex-wrap gap-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    <span>Submitted {{ $projectRequest->submitted_at?->format('M j, Y g:i A') }}</span>
                                    @if ($projectRequest->company_name)
                                        <span>{{ $projectRequest->company_name }}</span>
                                    @endif
                                    @if ($projectRequest->budget !== null)
                                        <span>${{ number_format((float) $projectRequest->budget, 2) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-3">
                                <a href="{{ route('client-requests.show', ['projectRequest' => $projectRequest]) }}" class="usn-btn-primary">Open Request</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No requests yet" description="Create a protected client request to start the project intake workflow." class="usn-card">
                        <x-slot name="actions">
                            @can('create', \App\Modules\ClientRequests\Models\ProjectRequest::class)
                                <a href="{{ route('client-requests.create') }}" class="usn-btn-primary">Submit Request</a>
                            @endcan
                        </x-slot>
                    </x-ui.empty-state>
                @endforelse
            </div>

            {{ $projectRequests->links() }}
        </div>
    </div>
</x-app-layout>
