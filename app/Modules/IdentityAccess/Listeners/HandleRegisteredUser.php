<?php

namespace App\Modules\IdentityAccess\Listeners;

use App\Enums\SecurityEventType;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Events\Registered;

class HandleRegisteredUser
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function handle(Registered $event): void
    {
        $this->securityEventService->record(
            SecurityEventType::EmailVerificationSent,
            $event->user,
            'info',
            ['context' => 'registration'],
        );

        $this->auditLogService->record(
            eventType: SecurityEventType::EmailVerificationSent->value,
            action: 'request_verification_email',
            actor: $event->user,
            auditable: $event->user,
        );
    }
}
