<?php

namespace App\Modules\IdentityAccess\Listeners;

use App\Enums\SecurityEventType;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Events\PasswordReset;

class HandlePasswordReset
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function handle(PasswordReset $event): void
    {
        $this->securityEventService->record(SecurityEventType::PasswordResetCompleted, $event->user);

        $this->auditLogService->record(
            eventType: SecurityEventType::PasswordResetCompleted->value,
            action: 'password_reset',
            actor: $event->user,
            auditable: $event->user,
        );
    }
}
