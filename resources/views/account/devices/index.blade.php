<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Device History"
            description="Monitor recognized devices and last-seen activity for this account."
            eyebrow="Security"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-4">
            <section class="usn-table-shell">
                <div class="usn-table-scroll">
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
                                <td colspan="7" class="py-10 text-center text-sm text-slate-500">No devices recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </section>

            {{ $devices->links() }}
        </div>
    </div>
</x-app-layout>
