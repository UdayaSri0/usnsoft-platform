<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Manage Account"
            description="{{ $account->name }} · {{ $account->email }}"
            eyebrow="Identity Access"
        >
            <x-slot name="actions">
                @if ($account->status === \App\Enums\AccountStatus::Active)
                    <span class="usn-badge-success">Active</span>
                @else
                    <span class="usn-badge-warning">{{ \Illuminate\Support\Str::headline($account->status->value) }}</span>
                @endif

                @if ($account->email_verified_at)
                    <span class="usn-badge-info">Verified</span>
                @else
                    <span class="usn-badge-warning">Unverified</span>
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            @if ($errors->has('password_reset'))
                <x-ui.alert tone="danger" :title="$errors->first('password_reset')" />
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <section class="usn-card">
                    <form method="POST" action="{{ route('admin.accounts.update', ['user' => $account->getKey()]) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        @include('admin.accounts._form', [
                            'account' => $account,
                            'createMode' => false,
                            'roles' => $roles,
                        ])

                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('admin.accounts.index') }}" class="usn-btn-secondary">Back to Accounts</a>
                            <x-primary-button>Save Changes</x-primary-button>
                        </div>
                    </form>
                </section>

                <div class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Operational Actions</h2>
                        <p class="mt-2 text-sm text-slate-500">Password resets, activation changes, and role updates remain audited.</p>

                        <div class="mt-5 space-y-4">
                            @can('initiatePasswordReset', $account)
                                <form method="POST" action="{{ route('admin.accounts.password-reset-link', ['user' => $account->getKey()]) }}">
                                    @csrf
                                    <x-secondary-button type="submit" class="w-full justify-center">Send Password Reset Link</x-secondary-button>
                                </form>
                            @endcan

                            @can('deactivateManaged', $account)
                                @if ($account->status === \App\Enums\AccountStatus::Active)
                                    <form method="POST" action="{{ route('admin.accounts.deactivate', ['user' => $account->getKey()]) }}" class="space-y-3">
                                        @csrf
                                        <div>
                                            <x-input-label for="reason" value="Deactivation Reason" />
                                            <x-textarea-input id="reason" name="reason" rows="3" class="mt-2 block w-full">{{ old('reason') }}</x-textarea-input>
                                        </div>
                                        <x-danger-button type="submit" class="w-full justify-center">Deactivate Account</x-danger-button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.accounts.reactivate', ['user' => $account->getKey()]) }}">
                                        @csrf
                                        <x-primary-button type="submit" class="w-full justify-center">Reactivate Account</x-primary-button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Recent Audit Trail</h2>
                        <div class="mt-4 space-y-3">
                            @forelse ($auditTrail as $entry)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $entry->event_type }}</p>
                                    <p class="mt-2 text-sm text-slate-700">{{ $entry->action }}</p>
                                    <p class="mt-2 text-xs text-slate-500">{{ $entry->created_at?->format('M j, Y g:i A') ?? 'Unknown timestamp' }}</p>
                                </div>
                            @empty
                                <x-ui.empty-state title="No audit entries yet" description="Privileged actions against this account will appear here." />
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
