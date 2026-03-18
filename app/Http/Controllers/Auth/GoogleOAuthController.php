<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\AuditSecurity\Services\LoginSecurityService;
use App\Modules\IdentityAccess\Services\SocialAuthService;
use App\Modules\IdentityAccess\Services\StaffMfaService;
use DomainException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleOAuthController extends Controller
{
    public function redirect(SocialAuthService $socialAuthService): RedirectResponse
    {
        if (! $socialAuthService->isGoogleConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not configured for this environment.',
            ]);
        }

        return $socialAuthService->redirectToGoogle();
    }

    public function callback(
        SocialAuthService $socialAuthService,
        LoginSecurityService $loginSecurityService,
        StaffMfaService $staffMfaService,
    ): RedirectResponse {
        try {
            $socialiteUser = $socialAuthService->fetchGoogleUser();
            $user = $socialAuthService->loginOrCreateFromGoogle($socialiteUser);
        } catch (DomainException $exception) {
            return redirect()->route('login')->withErrors([
                'email' => $exception->getMessage(),
            ]);
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication state was invalid. Please try again.',
            ]);
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication failed. Please try again later.',
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();
        $staffMfaService->clearSessionState(request()->session());
        $loginSecurityService->recordSuccessfulLogin($user, request()->session()->getId());

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
