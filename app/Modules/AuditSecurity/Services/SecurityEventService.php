<?php

namespace App\Modules\AuditSecurity\Services;

use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Models\SecurityEvent;
use Carbon\CarbonImmutable;

class SecurityEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        SecurityEventType|string $eventType,
        ?User $user = null,
        string $severity = 'info',
        array $context = [],
    ): SecurityEvent {
        $resolvedEventType = $eventType instanceof SecurityEventType
            ? $eventType->value
            : $eventType;

        return SecurityEvent::query()->create([
            'user_id' => $user?->getKey(),
            'event_type' => $resolvedEventType,
            'severity' => $severity,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'context' => $context,
            'occurred_at' => CarbonImmutable::now(),
        ]);
    }
}
