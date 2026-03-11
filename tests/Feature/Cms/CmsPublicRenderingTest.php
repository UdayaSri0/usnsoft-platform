<?php

namespace Tests\Feature\Cms;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\Pages\Services\CmsWorkflowService;
use Database\Seeders\CmsBlockDefinitionSeeder;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPublicRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_is_resolved_by_path_and_unpublished_page_returns_not_found(): void
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

        $page = $workflow->createPageWithDraft(
            actor: $superAdmin,
            pageAttributes: ['page_type' => 'custom'],
            versionAttributes: [
                'title' => 'Public Rendering',
                'slug' => 'public-rendering',
                'path' => '/public-rendering',
            ],
            blocks: [
                [
                    'block_type' => 'hero',
                    'data' => [
                        'title' => 'Public Rendering',
                    ],
                ],
            ],
        );

        $version = $page->currentDraftVersion()->firstOrFail();

        $workflow->submitForReview($version, $superAdmin);
        $workflow->approve($version, $superAdmin);
        $version->refresh();
        $workflow->confirmPreview($version, $superAdmin);
        $workflow->publishNow($version, $superAdmin);

        $this->get('/public-rendering')
            ->assertOk()
            ->assertSee('Public Rendering');

        $workflow->archive($version, $superAdmin);

        $this->get('/public-rendering')->assertNotFound();
    }
}
