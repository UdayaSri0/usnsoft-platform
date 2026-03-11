<x-guest-layout>
    <div>
        <h1 class="font-display text-2xl font-semibold text-slate-900">Create your USNsoft account</h1>
        <p class="mt-2 text-sm text-slate-600">Public signup creates a standard user account. Internal privileged accounts are provisioned by SuperAdmin only.</p>
    </div>

    @if (filled(config('services.google.client_id')))
        <div class="mt-5 space-y-3">
            <a href="{{ route('auth.google.redirect') }}" class="usn-btn-secondary w-full justify-center">Register with Google</a>
            <p class="text-center text-xs font-semibold uppercase tracking-wide text-slate-400">or create with email</p>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5" x-data="{ showPassword: false, showConfirm: false }">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-20" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-lg px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"></button>
            </div>
            <p class="mt-1 text-xs text-slate-500">Use at least 12 characters with mixed case, numbers, and symbols.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <div class="relative mt-1">
                <x-text-input id="password_confirmation" class="block w-full pe-20" x-bind:type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-lg px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100" @click="showConfirm = !showConfirm" x-text="showConfirm ? 'Hide' : 'Show'"></button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Register') }}</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-sky-700 hover:text-sky-900">Log in</a>
    </p>
</x-guest-layout>
