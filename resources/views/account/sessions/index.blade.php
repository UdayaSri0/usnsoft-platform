<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="usn-heading">Session History</h2>
            <p class="usn-subheading">Review active and historical sessions for this account.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'other-sessions-invalidated')
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">Other sessions were logged out successfully.</div>
            @elseif (session('status') === 'no-other-sessions')
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">No other active sessions were found.</div>
            @endif

            <section class="usn-card">
                <h3 class="font-display text-lg font-semibold text-slate-900">Logout Other Sessions</h3>
                <form method="POST" action="{{ route('account.sessions.destroy-others') }}" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                    @csrf
                    <div class="w-full sm:max-w-sm">
                        <x-input-label for="logout_other_password" :value="__('Confirm password')" />
                        <x-text-input id="logout_other_password" type="password" name="password" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                    <x-primary-button>Logout Other Sessions</x-primary-button>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                                <td colspan="6" class="text-center text-sm text-slate-500">No session history available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            {{ $history->links() }}
        </div>
    </div>
</x-app-layout>
