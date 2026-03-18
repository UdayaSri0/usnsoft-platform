<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Services\MfaService;
use App\Modules\IdentityAccess\Services\StaffMfaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MfaController extends Controller
{
    public function __construct(
        private readonly MfaService $mfaService,
        private readonly StaffMfaService $staffMfaService,
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.security.mfa', [
            'user' => $user,
            'activeMethod' => $this->mfaService->activeMethod($user),
            'pendingEnrollment' => $this->mfaService->enrollmentData($user),
            'requiresMfa' => $this->staffMfaService->requiresMfa($user),
            'enforcementEnabled' => $this->staffMfaService->enforcementEnabled(),
            'recoveryCodes' => session('mfa_recovery_codes', []),
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        try {
            $this->mfaService->startEnrollment($request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'mfa' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('account.security.mfa.edit')
            ->with('status', 'mfa-enrollment-started');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        try {
            $recoveryCodes = $this->mfaService->confirmEnrollment($request->user(), (string) $validated['code']);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' => $exception->getMessage(),
                ]);
        }

        $this->staffMfaService->markChallengePassed($request->session());

        return redirect()
            ->route('account.security.mfa.edit')
            ->with('status', 'mfa-enabled')
            ->with('mfa_recovery_codes', $recoveryCodes);
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->staffMfaService->requiresMfa($user)) {
            return redirect()->route('dashboard');
        }

        if (! $this->mfaService->activeMethod($user)) {
            return redirect()
                ->route('account.security.mfa.edit')
                ->with('status', 'mfa-setup-required');
        }

        return view('account.security.mfa-challenge', [
            'user' => $user,
        ]);
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        try {
            $result = $this->mfaService->verifyChallenge($request->user(), (string) $validated['code']);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' => $exception->getMessage(),
                ]);
        }

        $this->staffMfaService->markChallengePassed($request->session());
        $redirectTo = $this->staffMfaService->pullIntendedUrl(
            $request->session(),
            route('dashboard', absolute: false),
        );

        return redirect()
            ->to($redirectTo)
            ->with('status', $result['used_recovery_code'] ? 'mfa-recovery-code-used' : 'mfa-challenge-passed');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        try {
            $codes = $this->mfaService->regenerateRecoveryCodes($request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'recovery_codes' => $exception->getMessage(),
            ]);
        }

        return back()
            ->with('status', 'mfa-recovery-codes-regenerated')
            ->with('mfa_recovery_codes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        try {
            $this->mfaService->disable(
                actor: $request->user(),
                target: $request->user(),
                reason: 'self-service disable',
            );
        } catch (AuthorizationException $exception) {
            return back()->withErrors([
                'mfa' => $exception->getMessage(),
            ]);
        }

        $this->staffMfaService->clearSessionState($request->session());

        return back()->with('status', 'mfa-disabled');
    }
}
