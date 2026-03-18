<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Multi-Factor Authentication"
            description="TOTP setup, recovery code management, and staff MFA compliance."
            eyebrow="Account Security"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            @if (session('status'))
                <x-ui.alert tone="success" :title="session('status')" />
            @endif

            @if ($errors->has('mfa'))
                <x-ui.alert tone="danger" :title="$errors->first('mfa')" />
            @endif

            @if ($requiresMfa && $enforcementEnabled && ! $activeMethod)
                <x-ui.alert tone="warning" title="MFA is required for this account">
                    Internal staff access is locked behind MFA. Complete setup below before continuing through protected staff routes.
                </x-ui.alert>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <section class="usn-card space-y-5">
                    <div>
                        <h2 class="font-display text-2xl font-semibold text-slate-950">Authenticator app setup</h2>
                        <p class="mt-2 text-sm text-slate-600">Use any RFC 6238 TOTP app such as 1Password, Google Authenticator, Bitwarden, Authy, or Microsoft Authenticator.</p>
                    </div>

                    @if ($activeMethod)
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Enabled</p>
                            <p class="mt-2 text-sm text-emerald-900">TOTP is active for this account.</p>
                            <p class="mt-2 text-xs text-emerald-700">Last verified: {{ $activeMethod->last_verified_at?->format('M j, Y g:i A') ?? 'Not yet recorded' }}</p>
                        </div>
                    @elseif ($pendingEnrollment)
                        <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Setup secret</p>
                                <p class="mt-2 break-all font-mono text-sm text-slate-900">{{ $pendingEnrollment['secret'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Provisioning URI</p>
                                <p class="mt-2 break-all font-mono text-xs text-slate-700">{{ $pendingEnrollment['provisioning_uri'] }}</p>
                            </div>

                            <form method="POST" action="{{ route('account.security.mfa.confirm') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="mfa_code" value="Authenticator Code" />
                                    <x-text-input id="mfa_code" name="code" inputmode="numeric" class="mt-2 block w-full" :value="old('code')" required />
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>
                                <x-primary-button>Enable MFA</x-primary-button>
                            </form>
                        </div>
                    @else
                        <x-ui.alert tone="info" title="MFA not set up yet">
                            Start setup to generate a TOTP secret and confirm it with your authenticator app.
                        </x-ui.alert>
                    @endif

                    <div class="flex flex-wrap gap-3">
                        @if (! $pendingEnrollment && ! $activeMethod)
                            <form method="POST" action="{{ route('account.security.mfa.start') }}">
                                @csrf
                                <x-primary-button type="submit">Start MFA Setup</x-primary-button>
                            </form>
                        @endif

                        @if ($activeMethod)
                            <form method="POST" action="{{ route('account.security.mfa.recovery-codes.regenerate') }}">
                                @csrf
                                <x-secondary-button type="submit">Regenerate Recovery Codes</x-secondary-button>
                            </form>
                        @endif
                    </div>
                </section>

                <div class="space-y-6">
                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Recovery Codes</h2>
                        <p class="mt-2 text-sm text-slate-600">These codes bypass the authenticator challenge once each. Store them outside the browser.</p>

                        @if ($recoveryCodes !== [])
                            <div class="mt-4 grid gap-2 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                @foreach ($recoveryCodes as $recoveryCode)
                                    <code class="rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900">{{ $recoveryCode }}</code>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 text-sm text-slate-500">Recovery codes are shown only when first generated or regenerated.</p>
                        @endif
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Policy</h2>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                            <li>Internal staff accounts are expected to keep MFA enabled.</li>
                            <li>Use password-confirmed recovery code regeneration if the stored set is compromised.</li>
                            <li>Required staff MFA cannot be self-disabled while enforcement is active.</li>
                        </ul>

                        @if ($activeMethod && (! $requiresMfa || ! $enforcementEnabled))
                            <form method="POST" action="{{ route('account.security.mfa.disable') }}" class="mt-6">
                                @csrf
                                <x-danger-button type="submit">Disable MFA</x-danger-button>
                            </form>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
