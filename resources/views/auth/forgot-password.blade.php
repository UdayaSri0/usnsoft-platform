<x-guest-layout>
    <div>
        <h1 class="font-display text-2xl font-semibold text-slate-900">Reset your password</h1>
        <p class="mt-2 text-sm text-slate-600">Enter the email linked to your account and we’ll send a secure reset link.</p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Email password reset link') }}</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Remembered your password?
        <a href="{{ route('login') }}" class="font-semibold text-sky-700 hover:text-sky-900">Back to login</a>
    </p>
</x-guest-layout>
