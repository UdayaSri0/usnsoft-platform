<?php

namespace App\Http\Middleware;

use App\Modules\IdentityAccess\Services\StaffMfaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalMfaCompliant
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $staffMfaService = app(StaffMfaService::class);

        if ($staffMfaService->shouldEnforceNow($user)) {
            if (! $request->routeIs('account.security.mfa.*')) {
                $staffMfaService->rememberIntendedUrl($request->session(), $request->fullUrl());

                return redirect()
                    ->route('account.security.mfa.edit')
                    ->with('status', 'mfa-setup-required');
            }

            return $next($request);
        }

        if ($staffMfaService->requiresChallenge($user, $request->session())) {
            if (! $request->routeIs('account.security.mfa.challenge*')) {
                $staffMfaService->rememberIntendedUrl($request->session(), $request->fullUrl());

                return redirect()
                    ->route('account.security.mfa.challenge')
                    ->with('status', 'mfa-challenge-required');
            }
        }

        return $next($request);
    }
}
