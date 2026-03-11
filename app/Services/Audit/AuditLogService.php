<?php

namespace App\Services\Audit;

use App\Modules\AuditSecurity\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $metadata
     * @param  list<string>  $tags
     */
    public function record(
        string $eventType,
        string $action,
        ?Authenticatable $actor = null,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        array $tags = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->getAuthIdentifier(),
            'event_type' => $eventType,
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'tags' => $tags,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'occurred_at' => CarbonImmutable::now(),
        ]);
    }
}
