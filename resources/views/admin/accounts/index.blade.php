<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Account Management"
            description="Role-aware internal account operations for customer users and privileged staff."
            eyebrow="Identity Access"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            <section class="usn-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <form method="GET" class="grid flex-1 gap-3 md:grid-cols-5">
                        <x-text-input name="q" :value="$filters['q']" placeholder="Search accounts" />

                        <x-select-input name="role">
                            <option value="">All roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected($filters['role'] === $role->name)>{{ $role->display_name ?? \Illuminate\Support\Str::headline($role->name) }}</option>
                            @endforeach
                        </x-select-input>

                        <x-select-input name="status">
                            <option value="">All statuses</option>
                            @foreach (\App\Enums\AccountStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                            @endforeach
                        </x-select-input>

                        <x-select-input name="verified">
                            <option value="">Verified?</option>
                            <option value="verified" @selected($filters['verified'] === 'verified')>Verified</option>
                            <option value="unverified" @selected($filters['verified'] === 'unverified')>Unverified</option>
                        </x-select-input>

                        <div class="flex gap-2">
                            <x-select-input name="internal">
                                <option value="">All account types</option>
                                <option value="0" @selected($filters['internal'] === '0')>User accounts</option>
                                <option value="1" @selected($filters['internal'] === '1')>Internal staff</option>
                            </x-select-input>
                            <x-primary-button>Filter</x-primary-button>
                        </div>
                    </form>

                    @if ($creatableRoles->isNotEmpty())
                        <a href="{{ route('admin.accounts.create') }}" class="usn-btn-primary">Create Account</a>
                    @endif
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="pb-3">Account</th>
                                <th class="pb-3">Roles</th>
                                <th class="pb-3">Verified</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Type</th>
                                <th class="pb-3">Created</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($accounts as $account)
                                <tr>
                                    <td class="py-4">
                                        <p class="font-semibold text-slate-900">{{ $account->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $account->email }}</p>
                                        @if ($account->phone)
                                            <p class="mt-1 text-xs text-slate-500">{{ $account->phone }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($account->roles as $role)
                                                <span class="usn-badge-info">{{ $role->display_name ?? \Illuminate\Support\Str::headline($role->name) }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        @if ($account->email_verified_at)
                                            <span class="usn-badge-success">Verified</span>
                                        @else
                                            <span class="usn-badge-warning">Unverified</span>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        @if ($account->status === \App\Enums\AccountStatus::Active)
                                            <span class="usn-badge-success">Active</span>
                                        @else
                                            <span class="usn-badge-warning">{{ \Illuminate\Support\Str::headline($account->status->value) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        @if ($account->is_internal)
                                            <span class="usn-badge-warning">Internal</span>
                                        @else
                                            <span class="usn-badge-muted">Public User</span>
                                        @endif
                                    </td>
                                    <td class="py-4">{{ $account->created_at?->format('M j, Y') ?? 'Unknown' }}</td>
                                    <td class="py-4 text-right">
                                        @can('manage', $account)
                                            <a href="{{ route('admin.accounts.edit', ['user' => $account->getKey()]) }}" class="usn-link">Manage</a>
                                        @else
                                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Restricted</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6">
                                        <x-ui.empty-state title="No accounts found" description="Customer users and internal staff accounts will appear here once provisioned." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $accounts->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
