<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="usn-heading">Device History</h2>
            <p class="usn-subheading">Monitor recognized devices and last-seen activity.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="usn-table">
                    <thead>
                        <tr>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                            <th>Last Login</th>
                            <th>Trusted</th>
                            <th>IP</th>
                            <th>Current Device</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr>
                                <td>{{ $device->first_seen_at }}</td>
                                <td>{{ $device->last_seen_at }}</td>
                                <td>{{ $device->last_login_at ?: '-' }}</td>
                                <td>{{ $device->is_trusted ? 'Yes' : 'No' }}</td>
                                <td>{{ $device->ip_address ?: '-' }}</td>
                                <td>
                                    @if ($device->device_fingerprint === $currentFingerprint)
                                        <span class="usn-badge-success">Current</span>
                                    @else
                                        <span class="usn-badge-muted">No</span>
                                    @endif
                                </td>
                                <td class="max-w-sm break-all">{{ $device->user_agent ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-sm text-slate-500">No devices recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            {{ $devices->links() }}
        </div>
    </div>
</x-app-layout>
