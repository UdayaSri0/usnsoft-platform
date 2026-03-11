<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Overview') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (! $user->hasVerifiedEmail())
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-800">
                    <p class="font-semibold">{{ __('Email verification is required for protected requests and downloads.') }}</p>
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                        @csrf
                        <x-primary-button>{{ __('Resend verification email') }}</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-2">
                    <p><strong>{{ __('Name:') }}</strong> {{ $user->name }}</p>
                    <p><strong>{{ __('Email:') }}</strong> {{ $user->email }}</p>
                    <p><strong>{{ __('Phone:') }}</strong> {{ $user->phone ?: '-' }}</p>
                    <p><strong>{{ __('Email Verified:') }}</strong> {{ $user->hasVerifiedEmail() ? __('Yes') : __('No') }}</p>
                    <p><strong>{{ __('Account Status:') }}</strong> {{ $user->status->value }}</p>
                    <p><strong>{{ __('MFA Enabled:') }}</strong> {{ $user->mfa_enabled_at ? __('Yes') : __('No (foundation ready)') }}</p>
                    <p><strong>{{ __('Last Login:') }}</strong> {{ $user->last_login_at ?: '-' }}</p>
                    <p><strong>{{ __('Last Login IP:') }}</strong> {{ $user->last_login_ip ?: '-' }}</p>
                    @if ($user->deletion_requested_at)
                        <p class="text-amber-700"><strong>{{ __('Deletion Requested At:') }}</strong> {{ $user->deletion_requested_at }}</p>
                    @endif
                    <div class="pt-2 flex items-center gap-4">
                        <a class="text-sm text-indigo-700 underline" href="{{ route('account.sessions.index') }}">{{ __('View sessions') }}</a>
                        <a class="text-sm text-indigo-700 underline" href="{{ route('account.devices.index') }}">{{ __('View devices') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
