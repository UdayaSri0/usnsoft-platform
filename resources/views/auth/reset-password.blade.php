<x-guest-layout>
    <div>
        <h1 class="font-display text-2xl font-semibold text-slate-900">Choose a new password</h1>
        <p class="mt-2 text-sm text-slate-600">Set a strong password to secure your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5" x-data="{ showPassword: false, showConfirm: false }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" />
            <div class="relative mt-2">
                <x-text-input id="password" class="block w-full pe-20" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-lg px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <div class="relative mt-2">
                <x-text-input id="password_confirmation" class="block w-full pe-20" x-bind:type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-lg px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100" @click="showConfirm = !showConfirm" x-text="showConfirm ? 'Hide' : 'Show'"></button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Reset password') }}</x-primary-button>
    </form>
</x-guest-layout>
