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
use App\Http\Middleware\TrackSessionActivity;
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
        $middleware->alias([
            'active' => EnsureActiveAccount::class,
            'admin' => EnsureAdminOnly::class,
            'admin.panel' => EnsureAdminPanelAccess::class,
            'internal' => EnsureInternalStaff::class,
            'internal.mfa' => EnsureInternalMfaCompliant::class,
            'permission' => EnsurePermission::class,
            'role' => EnsureRole::class,
            'superadmin' => EnsureSuperAdminOnly::class,
            'session.track' => TrackSessionActivity::class,
            'verified.feature' => EnsureVerifiedForProtectedFeatures::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
