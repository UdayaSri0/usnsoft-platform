<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Device History') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left">
                                    <th class="py-2 pe-4">{{ __('First Seen') }}</th>
                                    <th class="py-2 pe-4">{{ __('Last Seen') }}</th>
                                    <th class="py-2 pe-4">{{ __('Last Login') }}</th>
                                    <th class="py-2 pe-4">{{ __('Trusted') }}</th>
                                    <th class="py-2 pe-4">{{ __('IP') }}</th>
                                    <th class="py-2 pe-4">{{ __('Current Device') }}</th>
                                    <th class="py-2">{{ __('User Agent') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($devices as $device)
                                    <tr>
                                        <td class="py-2 pe-4">{{ $device->first_seen_at }}</td>
                                        <td class="py-2 pe-4">{{ $device->last_seen_at }}</td>
                                        <td class="py-2 pe-4">{{ $device->last_login_at ?: '-' }}</td>
                                        <td class="py-2 pe-4">{{ $device->is_trusted ? __('Yes') : __('No') }}</td>
                                        <td class="py-2 pe-4">{{ $device->ip_address ?: '-' }}</td>
                                        <td class="py-2 pe-4">
                                            {{ $device->device_fingerprint === $currentFingerprint ? __('Yes') : __('No') }}
                                        </td>
                                        <td class="py-2 break-all">{{ $device->user_agent ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 text-gray-500">{{ __('No devices recorded yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $devices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
