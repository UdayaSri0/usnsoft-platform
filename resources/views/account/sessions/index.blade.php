<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Device & Session History') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status') === 'other-sessions-invalidated')
                        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-700">
                            {{ __('Other sessions were logged out successfully.') }}
                        </div>
                    @elseif (session('status') === 'no-other-sessions')
                        <div class="mb-4 rounded border border-gray-300 bg-gray-50 p-3 text-sm text-gray-700">
                            {{ __('No other active sessions were found.') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('account.sessions.destroy-others') }}" class="mb-6 space-y-2">
                        @csrf
                        <x-input-label for="logout_other_password" :value="__('Confirm password to logout other sessions')" />
                        <div class="flex flex-col sm:flex-row gap-2">
                            <x-text-input id="logout_other_password" type="password" name="password" class="block w-full sm:w-96" required />
                            <x-primary-button>{{ __('Logout Other Sessions') }}</x-primary-button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left">
                                    <th class="py-2 pe-4">{{ __('Logged In') }}</th>
                                    <th class="py-2 pe-4">{{ __('Last Activity') }}</th>
                                    <th class="py-2 pe-4">{{ __('IP') }}</th>
                                    <th class="py-2 pe-4">{{ __('Current') }}</th>
                                    <th class="py-2 pe-4">{{ __('Invalidated') }}</th>
                                    <th class="py-2">{{ __('User Agent') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($history as $session)
                                    <tr>
                                        <td class="py-2 pe-4">{{ $session->logged_in_at }}</td>
                                        <td class="py-2 pe-4">{{ $session->last_activity_at ?? '-' }}</td>
                                        <td class="py-2 pe-4">{{ $session->ip_address ?? '-' }}</td>
                                        <td class="py-2 pe-4">
                                            {{ $session->session_identifier === $currentSessionId ? __('Yes') : __('No') }}
                                        </td>
                                        <td class="py-2 pe-4">{{ $session->invalidated_at ? __('Yes') : __('No') }}</td>
                                        <td class="py-2 break-all">{{ $session->user_agent ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-gray-500">{{ __('No session history available yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $history->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
