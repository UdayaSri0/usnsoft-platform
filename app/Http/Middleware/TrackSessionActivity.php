<?php

namespace App\Http\Middleware;

use App\Modules\AuditSecurity\Services\LoginSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSessionActivity
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if ($user && $request->hasSession()) {
            app(LoginSecurityService::class)->touchSession($user, $request->session()->getId());
        }

        return $response;
    }
}
