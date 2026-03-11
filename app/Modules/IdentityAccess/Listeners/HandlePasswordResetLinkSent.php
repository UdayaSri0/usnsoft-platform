<?php

namespace App\Modules\IdentityAccess\Listeners;

use App\Enums\SecurityEventType;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Events\PasswordResetLinkSent;

class HandlePasswordResetLinkSent
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function handle(PasswordResetLinkSent $event): void
    {
        $this->securityEventService->record(SecurityEventType::PasswordResetRequested, $event->user);

        $this->auditLogService->record(
            eventType: SecurityEventType::PasswordResetRequested->value,
            action: 'request_password_reset',
            actor: $event->user,
            auditable: $event->user,
        );
    }
}
