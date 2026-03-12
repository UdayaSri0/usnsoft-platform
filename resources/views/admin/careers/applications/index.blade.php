<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Job Applications" description="Protected applicant review workspace." eyebrow="Operations" />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <form method="GET" class="grid gap-3 md:grid-cols-4">
                    <x-text-input name="q" :value="$filters['q']" placeholder="Search applicant" />
                    <x-select-input name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="job">
                        <option value="">All jobs</option>
                        @foreach ($jobs as $job)
                            <option value="{{ $job->slug }}" @selected($filters['job'] === $job->slug)>{{ $job->title }}</option>
                        @endforeach
                    </x-select-input>
                    <div class="flex justify-end">
                        <x-primary-button>Filter</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="usn-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="pb-3">Applicant</th>
                                <th class="pb-3">Job</th>
                                <th class="pb-3">Submitted</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($applications as $application)
                                <tr>
                                    <td class="py-4">
                                        <p class="font-semibold text-slate-900">{{ $application->full_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $application->email }}</p>
                                    </td>
                                    <td class="py-4">{{ $application->job?->title ?? 'Unknown role' }}</td>
                                    <td class="py-4">{{ $application->submitted_at?->format('M j, Y g:i A') }}</td>
                                    <td class="py-4"><span class="usn-badge-info">{{ \Illuminate\Support\Str::headline($application->status->value) }}</span></td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('admin.careers.applications.show', ['application' => $application->getKey()]) }}" class="usn-link">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6">
                                        <x-ui.empty-state title="No applications yet" description="Protected applicant submissions will appear here." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $applications->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
