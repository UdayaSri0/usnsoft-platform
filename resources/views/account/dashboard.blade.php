<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="usn-heading">Account Overview</h2>
            <p class="usn-subheading">Security status, profile summary, and quick actions.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (! $user->hasVerifiedEmail())
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-800">
                    <p class="font-semibold">Email verification is required for protected requests and downloads.</p>
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                        @csrf
                        <x-primary-button>Resend verification email</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="grid gap-4 lg:grid-cols-3">
                <section class="usn-card lg:col-span-2">
                    <h3 class="font-display text-lg font-semibold text-slate-900">Profile</h3>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</dt><dd class="text-sm text-slate-800">{{ $user->name }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt><dd class="text-sm text-slate-800">{{ $user->email }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</dt><dd class="text-sm text-slate-800">{{ $user->phone ?: '-' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt><dd class="text-sm text-slate-800">{{ $user->status->value }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Verified</dt><dd class="text-sm text-slate-800">{{ $user->hasVerifiedEmail() ? 'Yes' : 'No' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">MFA</dt><dd class="text-sm text-slate-800">{{ $user->mfa_enabled_at ? 'Enabled' : 'Ready (not enabled)' }}</dd></div>
                    </dl>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a class="usn-btn-secondary" href="{{ route('profile.edit') }}">Manage Profile</a>
                        <a class="usn-btn-secondary" href="{{ route('account.sessions.index') }}">Session History</a>
                        <a class="usn-btn-secondary" href="{{ route('account.devices.index') }}">Device History</a>
                    </div>
                </section>

                <section class="usn-card">
                    <h3 class="font-display text-lg font-semibold text-slate-900">Recent Login</h3>
                    <p class="mt-4 text-sm text-slate-600">Last login: {{ $user->last_login_at ?: '-' }}</p>
                    <p class="mt-2 text-sm text-slate-600">IP: {{ $user->last_login_ip ?: '-' }}</p>
                    @if ($user->deletion_requested_at)
                        <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Deletion requested at {{ $user->deletion_requested_at }}</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
