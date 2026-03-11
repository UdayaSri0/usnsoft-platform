<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityEventType;
use App\Http\Controllers\Controller;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(
        Request $request,
        SecurityEventService $securityEventService,
        AuditLogService $auditLogService,
    ): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        $securityEventService->record(SecurityEventType::EmailVerificationSent, $request->user());

        $auditLogService->record(
            eventType: SecurityEventType::EmailVerificationSent->value,
            action: 'resend_verification_email',
            actor: $request->user(),
            auditable: $request->user(),
        );

        return back()->with('status', 'verification-link-sent');
    }
}
