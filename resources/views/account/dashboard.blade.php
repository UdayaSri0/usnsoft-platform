<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Account Overview"
            description="Security status, profile summary, and quick access to self-service account tools."
            eyebrow="Account"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (! $user->hasVerifiedEmail())
                <x-ui.alert tone="warning" title="Email verification required">
                    Email verification is required for protected requests and downloads.
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                        @csrf
                        <x-primary-button>Resend verification email</x-primary-button>
                    </form>
                </x-ui.alert>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="usn-card lg:col-span-2">
                    <h3 class="font-display text-xl font-semibold text-slate-950">Profile summary</h3>
                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Name</dt><dd class="mt-2 text-sm text-slate-800">{{ $user->name }}</dd></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email</dt><dd class="mt-2 text-sm text-slate-800">{{ $user->email }}</dd></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Phone</dt><dd class="mt-2 text-sm text-slate-800">{{ $user->phone ?: '-' }}</dd></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Status</dt><dd class="mt-2 text-sm text-slate-800">{{ $user->status->value }}</dd></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Verified</dt><dd class="mt-2 text-sm text-slate-800">{{ $user->hasVerifiedEmail() ? 'Yes' : 'No' }}</dd></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">MFA</dt><dd class="mt-2 text-sm text-slate-800">{{ $user->mfa_enabled_at ? 'Enabled' : 'Ready (not enabled)' }}</dd></div>
                    </dl>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a class="usn-btn-secondary" href="{{ route('profile.edit') }}">Manage Profile</a>
                        <a class="usn-btn-secondary" href="{{ route('account.sessions.index') }}">Session History</a>
                        <a class="usn-btn-secondary" href="{{ route('account.devices.index') }}">Device History</a>
                        @if ($user->hasPermission('requests.viewOwn'))
                            <a class="usn-btn-secondary" href="{{ route('client-requests.index') }}">My Requests</a>
                        @endif
                    </div>
                </section>

                <section class="usn-card">
                    <h3 class="font-display text-xl font-semibold text-slate-950">Recent Login</h3>
                    <p class="mt-4 text-sm text-slate-600">Last login: {{ $user->last_login_at ?: '-' }}</p>
                    <p class="mt-2 text-sm text-slate-600">IP: {{ $user->last_login_ip ?: '-' }}</p>
                    @if ($user->deletion_requested_at)
                        <p class="mt-4"><span class="usn-badge-warning">Deletion requested at {{ $user->deletion_requested_at }}</span></p>
                    @endif
                </section>
            </div>

            @if ($user->hasPermission('requests.viewOwn'))
                <section class="usn-card">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="font-display text-xl font-semibold text-slate-950">Client request tracking</h3>
                            <p class="mt-2 text-sm text-slate-600">Protected intake requests, visible workflow updates, and authorized attachments all stay in your account area.</p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <span class="usn-badge-info">{{ $projectRequestCount }} request{{ $projectRequestCount === 1 ? '' : 's' }}</span>
                            @can('create', \App\Modules\ClientRequests\Models\ProjectRequest::class)
                                <a href="{{ route('client-requests.create') }}" class="usn-btn-primary">New Request</a>
                            @endcan
                            <a href="{{ route('client-requests.index') }}" class="usn-btn-secondary">View All</a>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentProjectRequests as $projectRequest)
                            <a href="{{ route('client-requests.show', ['projectRequest' => $projectRequest]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $projectRequest->project_title }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ $projectRequest->project_summary }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <x-client-request-status-badge :status="$projectRequest->currentStatus" />
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $projectRequest->submitted_at?->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <x-ui.empty-state title="No requests submitted yet" description="When you submit a protected project request, the current status and visible updates will appear here." />
                        @endforelse
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
