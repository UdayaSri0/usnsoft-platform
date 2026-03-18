<?php

use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsureAdminOnly;
use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\EnsureInternalMfaCompliant;
use App\Http\Middleware\EnsureInternalStaff;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSuperAdminOnly;
use App\Http\Middleware\EnsureVerifiedForProtectedFeatures;
use App\Http\Middleware\ApplySecurityHeaders;
use App\Http\Middleware\EnforceSessionTimeout;
use App\Http\Middleware\TrackSessionActivity;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = env('TRUSTED_PROXIES');
        if ($trustedProxies !== null) {
            $middleware->trustProxies(
                at: $trustedProxies === '*'
                    ? '*'
                    : array_values(array_filter(array_map('trim', explode(',', $trustedProxies)))),
                headers: TrustProxies::HEADER_X_FORWARDED_FOR
                    | TrustProxies::HEADER_X_FORWARDED_HOST
                    | TrustProxies::HEADER_X_FORWARDED_PORT
                    | TrustProxies::HEADER_X_FORWARDED_PROTO
                    | TrustProxies::HEADER_X_FORWARDED_PREFIX,
            );
        }

        $trustedHosts = env('TRUSTED_HOSTS');
        if (is_string($trustedHosts) && trim($trustedHosts) !== '') {
            $middleware->trustHosts(
                at: static fn (): array => array_values(array_filter(array_map('trim', explode(',', $trustedHosts)))),
            );
        }

        $middleware->web(append: [
            ApplySecurityHeaders::class,
        ]);

        $middleware->alias([
            'active' => EnsureActiveAccount::class,
            'admin' => EnsureAdminOnly::class,
            'admin.panel' => EnsureAdminPanelAccess::class,
            'internal' => EnsureInternalStaff::class,
            'internal.mfa' => EnsureInternalMfaCompliant::class,
            'permission' => EnsurePermission::class,
            'role' => EnsureRole::class,
            'session.timeout' => EnforceSessionTimeout::class,
            'superadmin' => EnsureSuperAdminOnly::class,
            'session.track' => TrackSessionActivity::class,
            'verified.feature' => EnsureVerifiedForProtectedFeatures::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
