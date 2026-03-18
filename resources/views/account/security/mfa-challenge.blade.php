<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="MFA Challenge"
            description="Complete the second-factor check to continue into protected staff areas."
            eyebrow="Account Security"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container max-w-2xl space-y-6">
            @if (session('status'))
                <x-ui.alert tone="warning" :title="session('status')" />
            @endif

            <section class="usn-card space-y-5">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-slate-950">Enter your code</h2>
                    <p class="mt-2 text-sm text-slate-600">Use your authenticator app code or one recovery code. Recovery codes are consumed after use.</p>
                </div>

                <form method="POST" action="{{ route('account.security.mfa.challenge.verify') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="challenge_code" value="Authenticator or Recovery Code" />
                        <x-text-input id="challenge_code" name="code" class="mt-2 block w-full" :value="old('code')" required autofocus />
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-primary-button>Verify</x-primary-button>
                        <a href="{{ route('account.security.mfa.edit') }}" class="usn-btn-secondary">Back to MFA Settings</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
