<?php

namespace App\Http\Middleware;

use App\Enums\CoreRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminOnly
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(CoreRole::SuperAdmin)) {
            abort(403);
        }

        return $next($request);
    }
}
