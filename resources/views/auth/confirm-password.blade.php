<x-guest-layout>
    <div>
        <h1 class="font-display text-2xl font-semibold text-slate-900">Confirm your password</h1>
        <p class="mt-2 text-sm text-slate-600">This action requires additional verification.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">Confirm</x-primary-button>
    </form>
</x-guest-layout>
