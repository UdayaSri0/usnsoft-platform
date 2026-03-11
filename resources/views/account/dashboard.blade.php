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
        </div>
    </div>
</x-app-layout>
