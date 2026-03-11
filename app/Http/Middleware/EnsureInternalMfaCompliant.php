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

        if (app(StaffMfaService::class)->shouldEnforceNow($user)) {
            abort(403, 'MFA setup is required for internal staff accounts.');
        }

        return $next($request);
    }
}
