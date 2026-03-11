<x-guest-layout>
    <div>
        <h1 class="font-display text-2xl font-semibold text-slate-900">Sign in to USNsoft</h1>
        <p class="mt-2 text-sm text-slate-600">Use your account to access project requests, protected downloads, and internal workflows.</p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    @if (filled(config('services.google.client_id')))
        <div class="mt-5 space-y-3">
            <a href="{{ route('auth.google.redirect') }}" class="usn-btn-secondary w-full justify-center">Continue with Google</a>
            <p class="text-center text-xs font-semibold uppercase tracking-wide text-slate-400">or use email credentials</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-sky-700 hover:text-sky-900" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-20" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-lg px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"></button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
            <span>{{ __('Remember this device') }}</span>
        </label>

        <x-primary-button class="w-full justify-center">{{ __('Log in') }}</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        No account yet?
        <a href="{{ route('register') }}" class="font-semibold text-sky-700 hover:text-sky-900">Create one</a>
    </p>
</x-guest-layout>
