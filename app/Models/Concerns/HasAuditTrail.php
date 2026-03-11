<?php

namespace App\Models\Concerns;

use App\Services\Audit\AuditLogService;

trait HasAuditTrail
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $metadata
     */
    public function logAudit(
        string $eventType,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): void {
        app(AuditLogService::class)->record(
            eventType: $eventType,
            action: $action,
            auditable: $this,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: $metadata,
        );
    }
}
