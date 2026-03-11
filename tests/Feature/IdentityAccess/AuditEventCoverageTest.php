<?php

namespace Tests\Feature\IdentityAccess;

use App\Enums\CoreRole;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Services\RoleAssignmentService;
use App\Modules\IdentityAccess\Services\RolePermissionService;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditEventCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_and_permission_changes_are_logged(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $superAdmin = User::factory()->create();
        $target = User::factory()->create();

        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $editorRole = Role::query()->where('name', CoreRole::Editor->value)->firstOrFail();
        $superAdmin->assignRole($superAdminRole);

        app(RoleAssignmentService::class)->assign($superAdmin, $target, $editorRole);

        $permissionIds = Permission::query()
            ->whereIn('name', ['profile.view', 'profile.update'])
            ->pluck('id')
            ->all();

        app(RolePermissionService::class)->syncPermissions($superAdmin, $editorRole, $permissionIds);

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::RoleChanged->value,
            'user_id' => $target->getKey(),
        ]);

        $this->assertDatabaseHas('security_events', [
            'event_type' => SecurityEventType::PermissionChanged->value,
            'user_id' => $superAdmin->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => SecurityEventType::RoleChanged->value,
            'action' => 'assign_role',
            'actor_id' => $superAdmin->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => SecurityEventType::PermissionChanged->value,
            'action' => 'sync_role_permissions',
            'actor_id' => $superAdmin->getKey(),
        ]);
    }
}
