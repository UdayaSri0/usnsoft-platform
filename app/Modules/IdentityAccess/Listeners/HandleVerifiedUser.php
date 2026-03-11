<?php

namespace App\Modules\IdentityAccess\Listeners;

use App\Enums\SecurityEventType;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Events\Verified;

class HandleVerifiedUser
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function handle(Verified $event): void
    {
        $this->securityEventService->record(SecurityEventType::EmailVerificationCompleted, $event->user);

        $this->auditLogService->record(
            eventType: SecurityEventType::EmailVerificationCompleted->value,
            action: 'verify_email',
            actor: $event->user,
            auditable: $event->user,
        );
    }
}
