<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Careers" description="Job listings, approvals, and applicant review." eyebrow="Content" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <form method="GET" class="grid flex-1 gap-3 md:grid-cols-4 xl:grid-cols-5">
                        <x-text-input name="q" :value="$filters['q']" placeholder="Search jobs" />
                        <x-select-input name="status">
                            <option value="">All statuses</option>
                            @foreach ($workflowStates as $state)
                                <option value="{{ $state->value }}" @selected($filters['status'] === $state->value)>{{ \Illuminate\Support\Str::headline($state->value) }}</option>
                            @endforeach
                        </x-select-input>
                        <x-select-input name="department">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}" @selected($filters['department'] === $department)>{{ $department }}</option>
                            @endforeach
                        </x-select-input>
                        <x-select-input name="employment_type">
                            <option value="">All types</option>
                            @foreach ($employmentTypes as $employmentType)
                                <option value="{{ $employmentType }}" @selected($filters['employmentType'] === $employmentType)>{{ $employmentType }}</option>
                            @endforeach
                        </x-select-input>
                        <div class="flex gap-2">
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.careers.applications.index') }}" class="usn-btn-secondary">Applications</a>
                        <a href="{{ route('admin.careers.create') }}" class="usn-btn-primary">New Job</a>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="pb-3">Role</th>
                                <th class="pb-3">Department</th>
                                <th class="pb-3">Type</th>
                                <th class="pb-3">Deadline</th>
                                <th class="pb-3">Applications</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($jobs as $job)
                                <tr>
                                    <td class="py-4">
                                        <p class="font-semibold text-slate-900">{{ $job->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $job->slug }}</p>
                                    </td>
                                    <td class="py-4">{{ $job->department ?? 'General' }}</td>
                                    <td class="py-4">{{ $job->employment_type ?? 'Not set' }}</td>
                                    <td class="py-4">{{ $job->deadline?->format('M j, Y g:i A') ?? 'Open until filled' }}</td>
                                    <td class="py-4">{{ $job->applications_count }}</td>
                                    <td class="py-4">
                                        <span class="usn-badge-warning">{{ $job->workflow_state->value }}</span>
                                        <span class="usn-badge-info">Approval: {{ $job->approval_state->value }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('admin.careers.edit', ['job' => $job->getKey()]) }}" class="usn-link">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6">
                                        <x-ui.empty-state title="No jobs yet" description="Create the first draft job listing to start the hiring workflow." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $jobs->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
