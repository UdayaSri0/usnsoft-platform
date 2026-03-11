<?php

namespace App\Http\Middleware;

use App\Enums\CoreRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOnly
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! $user->hasAnyRole([CoreRole::Admin, CoreRole::SuperAdmin])) {
            abort(403);
        }

        return $next($request);
    }
}
