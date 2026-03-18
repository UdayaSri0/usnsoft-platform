<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Security Center"
            description="Read-only operational visibility for authentication, MFA compliance, sessions, audit trails, and sensitive access signals."
            eyebrow="Security"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @if ($securityEvents->isNotEmpty())
                    <div class="usn-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Security Events</p>
                        <p class="mt-3 font-display text-3xl font-semibold text-slate-950">{{ $securityEvents->count() }}</p>
                        <p class="mt-2 text-sm text-slate-600">Recent authentication and security telemetry visible to this role.</p>
                    </div>
                @endif
                @if ($failedLoginAttempts->isNotEmpty())
                    <div class="usn-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Failed Logins</p>
                        <p class="mt-3 font-display text-3xl font-semibold text-slate-950">{{ $failedLoginAttempts->count() }}</p>
                        <p class="mt-2 text-sm text-slate-600">Recent denied credential attempts and suspicious spikes.</p>
                    </div>
                @endif
                @if ($recentSessions->isNotEmpty())
                    <div class="usn-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Sessions</p>
                        <p class="mt-3 font-display text-3xl font-semibold text-slate-950">{{ $recentSessions->count() }}</p>
                        <p class="mt-2 text-sm text-slate-600">Latest tracked sign-ins visible under current permissions.</p>
                    </div>
                @endif
                @if ($staffMfaUsers->isNotEmpty())
                    <div class="usn-card">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Staff MFA</p>
                        <p class="mt-3 font-display text-3xl font-semibold text-slate-950">{{ $staffMfaUsers->whereNotNull('mfa_enabled_at')->count() }}/{{ $staffMfaUsers->count() }}</p>
                        <p class="mt-2 text-sm text-slate-600">Visible internal accounts currently enrolled in MFA.</p>
                    </div>
                @endif
            </div>

            @if ($staffMfaUsers->isNotEmpty())
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Staff MFA Compliance</h2>
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-[0.18em] text-slate-500">
                                <tr>
                                    <th class="pb-3">Account</th>
                                    <th class="pb-3">Roles</th>
                                    <th class="pb-3">MFA</th>
                                    <th class="pb-3">Last Verified</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($staffMfaUsers as $staffUser)
                                    <tr>
                                        <td class="py-4">
                                            <p class="font-semibold text-slate-900">{{ $staffUser->name }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $staffUser->email }}</p>
                                        </td>
                                        <td class="py-4">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($staffUser->roles as $role)
                                                    <span class="usn-badge-info">{{ $role->display_name ?? \Illuminate\Support\Str::headline($role->name) }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            @if ($staffUser->mfa_enabled_at)
                                                <span class="usn-badge-success">Enabled</span>
                                            @else
                                                <span class="usn-badge-warning">Required / Not Enabled</span>
                                            @endif
                                        </td>
                                        <td class="py-4">{{ optional($staffUser->mfaMethods->first()?->last_verified_at)->format('M j, Y g:i A') ?? 'Not yet recorded' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if ($securityEvents->isNotEmpty())
                <section class="usn-card">
                    <h2 class="font-display text-xl font-semibold text-slate-950">Recent Security Events</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($securityEvents as $event)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $event->severity }}</p>
                                        <p class="mt-2 font-semibold text-slate-900">{{ is_string($event->event_type) ? $event->event_type : $event->event_type->value }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ $event->user?->email ?? 'No associated user' }}</p>
                                    </div>
                                    <p class="text-xs text-slate-500">{{ $event->occurred_at?->format('M j, Y g:i A') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid gap-6 xl:grid-cols-2">
                @if ($failedLoginAttempts->isNotEmpty())
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Failed Login Attempts</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($failedLoginAttempts as $attempt)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-semibold text-slate-900">{{ $attempt->email ?? $attempt->user?->email ?? 'Unknown account' }}</p>
                                    <p class="mt-1 text-sm text-slate-600">Reason: {{ \Illuminate\Support\Str::headline($attempt->reason) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $attempt->ip_address ?: 'No IP recorded' }} · {{ $attempt->occurred_at?->format('M j, Y g:i A') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($auditLogs->isNotEmpty())
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Recent Audit Logs</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($auditLogs as $log)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $log->event_type }}</p>
                                    <p class="mt-2 font-semibold text-slate-900">{{ $log->action }}</p>
                                    <p class="mt-1 text-sm text-slate-600">{{ $log->actor?->email ?? 'System / Unknown actor' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $log->occurred_at?->format('M j, Y g:i A') ?? $log->created_at?->format('M j, Y g:i A') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            @if ($recentSessions->isNotEmpty() || $recentDevices->isNotEmpty())
                <div class="grid gap-6 xl:grid-cols-2">
                    @if ($recentSessions->isNotEmpty())
                        <section class="usn-card">
                            <h2 class="font-display text-xl font-semibold text-slate-950">Recent Sessions</h2>
                            <div class="mt-4 space-y-3">
                                @foreach ($recentSessions as $session)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="font-semibold text-slate-900">{{ $session->user?->email ?? 'Unknown user' }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ $session->ip_address ?: 'No IP recorded' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $session->logged_in_at?->format('M j, Y g:i A') }} · {{ $session->is_current ? 'Current' : 'Historical' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($recentDevices->isNotEmpty())
                        <section class="usn-card">
                            <h2 class="font-display text-xl font-semibold text-slate-950">Recent Devices</h2>
                            <div class="mt-4 space-y-3">
                                @foreach ($recentDevices as $device)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="font-semibold text-slate-900">{{ $device->user?->email ?? 'Unknown user' }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ $device->device_label ?: 'Unlabelled device' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $device->last_seen_at?->format('M j, Y g:i A') }} · {{ $device->ip_address ?: 'No IP recorded' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            @endif

            <section class="usn-card">
                <h2 class="font-display text-xl font-semibold text-slate-950">Operational Runbooks</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    @foreach ($links as $link)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">{{ $link['label'] }}</p>
                            <code class="mt-2 block text-xs text-slate-600">{{ $link['path'] }}</code>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
