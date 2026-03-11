<?php

namespace Tests\Unit\Audit;

use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_structured_audit_log_entry(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $log = app(AuditLogService::class)->record(
            eventType: 'role.changed',
            action: 'assign_role',
            actor: $actor,
            auditable: $target,
            oldValues: ['role' => null],
            newValues: ['role' => 'editor'],
            metadata: ['source' => 'unit_test'],
            tags: ['identity', 'rbac'],
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->getKey(),
            'actor_id' => $actor->getKey(),
            'event_type' => 'role.changed',
            'event' => 'role.changed',
            'action' => 'assign_role',
            'auditable_type' => 'user',
            'auditable_id' => $target->getKey(),
        ]);
    }
}
