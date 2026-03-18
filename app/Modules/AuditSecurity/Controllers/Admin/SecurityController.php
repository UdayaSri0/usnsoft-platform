<?php

namespace App\Modules\AuditSecurity\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\AuditSecurity\Models\AuditLog;
use App\Modules\AuditSecurity\Models\FailedLoginAttempt;
use App\Modules\AuditSecurity\Models\SecurityEvent;
use App\Modules\AuditSecurity\Models\UserDevice;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->canAccess($user), 403);

        return view('admin.security.index', [
            'user' => $user,
            'securityEvents' => $user->can('viewAny', SecurityEvent::class)
                ? SecurityEvent::query()->with('user')->latest('occurred_at')->limit(20)->get()
                : collect(),
            'auditLogs' => $user->can('viewAny', AuditLog::class)
                ? AuditLog::query()->with('actor')->latest('occurred_at')->limit(20)->get()
                : collect(),
            'failedLoginAttempts' => $user->can('viewAny', FailedLoginAttempt::class)
                ? FailedLoginAttempt::query()->with('user')->latest('occurred_at')->limit(20)->get()
                : collect(),
            'recentSessions' => $user->can('viewAny', UserSessionHistory::class)
                ? UserSessionHistory::query()->with('user', 'device')->latest('logged_in_at')->limit(15)->get()
                : collect(),
            'recentDevices' => $user->can('viewAny', UserDevice::class)
                ? UserDevice::query()->with('user')->latest('last_seen_at')->limit(15)->get()
                : collect(),
            'staffMfaUsers' => $user->hasPermission('security.mfa.view')
                ? User::query()
                    ->with(['roles', 'mfaMethods' => fn ($query) => $query->whereNotNull('enabled_at')->latest('last_verified_at')])
                    ->where('is_internal', true)
                    ->orderByDesc('mfa_enabled_at')
                    ->orderBy('name')
                    ->limit(20)
                    ->get()
                : collect(),
            'links' => $this->runbookLinks(),
        ]);
    }

    private function canAccess(User $user): bool
    {
        return $user->hasPermission('security.logs.view')
            || $user->hasPermission('security.events.view')
            || $user->hasPermission('security.failedLogins.view')
            || $user->hasPermission('security.sessions.viewAny')
            || $user->hasPermission('security.devices.viewAny')
            || $user->hasPermission('security.mfa.view');
    }

    /**
     * @return Collection<int, array{label: string, path: string}>
     */
    private function runbookLinks(): Collection
    {
        return collect([
            ['label' => 'Deployment Overview', 'path' => base_path('docs/deployment-overview.md')],
            ['label' => 'Queue And Scheduler', 'path' => base_path('docs/runbooks/queue-and-scheduler.md')],
            ['label' => 'Security Baseline', 'path' => base_path('docs/security-baseline.md')],
        ]);
    }
}
