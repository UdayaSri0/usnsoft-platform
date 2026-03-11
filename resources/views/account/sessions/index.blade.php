<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Session History"
            description="Review active and historical sessions for this account."
            eyebrow="Security"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            @if (session('status') === 'other-sessions-invalidated')
                <x-ui.alert tone="success" title="Other sessions were logged out successfully." />
            @elseif (session('status') === 'no-other-sessions')
                <x-ui.alert tone="info" title="No other active sessions were found." />
            @endif

            <section class="usn-card">
                <h3 class="font-display text-xl font-semibold text-slate-950">Logout Other Sessions</h3>
                <form method="POST" action="{{ route('account.sessions.destroy-others') }}" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                    @csrf
                    <div class="w-full sm:max-w-sm">
                        <x-input-label for="logout_other_password" :value="__('Confirm password')" />
                        <x-text-input id="logout_other_password" type="password" name="password" class="mt-2 block w-full" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                    <x-primary-button>Logout Other Sessions</x-primary-button>
                </form>
            </section>

            <section class="usn-table-shell">
                <div class="usn-table-scroll">
                    <table class="usn-table">
                    <thead>
                        <tr>
                            <th>Logged In</th>
                            <th>Last Activity</th>
                            <th>IP</th>
                            <th>Current</th>
                            <th>Invalidated</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $session)
                            <tr>
                                <td>{{ $session->logged_in_at }}</td>
                                <td>{{ $session->last_activity_at ?? '-' }}</td>
                                <td>{{ $session->ip_address ?? '-' }}</td>
                                <td>
                                    @if ($session->session_identifier === $currentSessionId)
                                        <span class="usn-badge-success">Current</span>
                                    @else
                                        <span class="usn-badge-muted">No</span>
                                    @endif
                                </td>
                                <td>{{ $session->invalidated_at ? 'Yes' : 'No' }}</td>
                                <td class="max-w-sm break-all">{{ $session->user_agent ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm text-slate-500">No session history available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </section>

            {{ $history->links() }}
        </div>
    </div>
</x-app-layout>
