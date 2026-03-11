<?php

namespace Tests\Feature\Cms;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\Pages\Services\CmsWorkflowService;
use App\Modules\Pages\Services\PreviewTokenService;
use Database\Seeders\CmsBlockDefinitionSeeder;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsAuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_and_publish_activity_is_audited(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
            CmsBlockDefinitionSeeder::class,
        ]);

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->firstOrFail();
        $superAdmin->assignRole($superAdminRole);

        $workflow = app(CmsWorkflowService::class);
        $previewTokens = app(PreviewTokenService::class);

        $page = $workflow->createPageWithDraft(
            actor: $superAdmin,
            pageAttributes: ['page_type' => 'custom'],
            versionAttributes: [
                'title' => 'Audit Trail Page',
                'slug' => 'audit-trail-page',
                'path' => '/audit-trail-page',
            ],
            blocks: [
                [
                    'block_type' => 'hero',
                    'data' => ['title' => 'Audit Trail Page'],
                ],
            ],
        );

        $version = $page->currentDraftVersion()->firstOrFail();

        $token = $previewTokens->issue($version, $superAdmin);

        $this->get(route('cms.preview.show', ['version' => $version, 'token' => $token]))
            ->assertOk();

        $workflow->submitForReview($version, $superAdmin);
        $workflow->approve($version, $superAdmin);
        $version->refresh();
        $workflow->confirmPreview($version, $superAdmin);
        $workflow->publishNow($version, $superAdmin);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'cms.page.created',
            'auditable_type' => 'page',
            'auditable_id' => $page->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'cms.preview.generated',
            'auditable_type' => 'page_version',
            'auditable_id' => $version->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'cms.preview.accessed',
            'auditable_type' => 'page_version',
            'auditable_id' => $version->getKey(),
        ]);

        $this->assertDatabaseHas('approval_records', [
            'action' => 'publish',
            'approvable_type' => 'page_version',
            'approvable_id' => $version->getKey(),
        ]);
    }
}
