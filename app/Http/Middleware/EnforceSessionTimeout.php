<?php

namespace App\Http\Middleware;

use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Services\StaffMfaService;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionTimeout
{
    private const LAST_ACTIVITY_SESSION_KEY = 'security.session.last_activity_at';

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
        private readonly StaffMfaService $staffMfaService,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $request->hasSession() && $user->isInternalStaff()) {
            $timeoutMinutes = max(1, (int) config('security.session.internal_idle_timeout_minutes', 30));
            $lastActivity = $request->session()->get(self::LAST_ACTIVITY_SESSION_KEY);

            if (is_string($lastActivity) && $lastActivity !== '') {
                $lastActivityAt = CarbonImmutable::parse($lastActivity);

                if ($lastActivityAt->addMinutes($timeoutMinutes)->isPast()) {
                    $this->securityEventService->record('session.timeout', $user, 'warning', [
                        'last_activity_at' => $lastActivityAt->toIso8601String(),
                        'timeout_minutes' => $timeoutMinutes,
                        'route' => $request->route()?->getName(),
                    ]);

                    $this->auditLogService->record(
                        eventType: 'session.timeout',
                        action: 'expire_session',
                        actor: $user,
                        metadata: [
                            'last_activity_at' => $lastActivityAt->toIso8601String(),
                            'timeout_minutes' => $timeoutMinutes,
                            'route' => $request->route()?->getName(),
                        ],
                    );

                    Auth::guard('web')->logout();
                    $this->staffMfaService->clearSessionState($request->session());
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()
                        ->route('login')
                        ->withErrors([
                            'email' => 'Your internal session expired due to inactivity. Please sign in again.',
                        ]);
                }
            }
        }

        $response = $next($request);

        if ($request->user() && $request->hasSession()) {
            $request->session()->put(self::LAST_ACTIVITY_SESSION_KEY, CarbonImmutable::now()->toIso8601String());
        }

        return $response;
    }
}
