<x-guest-layout>
    <div>
        <h1 class="font-display text-2xl font-semibold text-slate-900">Verify your email</h1>
        <p class="mt-2 text-sm text-slate-600">Check your inbox and click the verification link to unlock protected features.</p>
    </div>

    @if (session('status') === 'verification-required-for-protected-features')
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            Email verification is required before accessing protected requests or downloads.
        </div>
    @endif

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="mt-6 space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center">Resend verification email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-secondary-button type="submit" class="w-full justify-center">Log out</x-secondary-button>
        </form>
    </div>
</x-guest-layout>
