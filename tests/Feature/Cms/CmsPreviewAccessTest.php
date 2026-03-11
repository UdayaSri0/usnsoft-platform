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

class CmsPreviewAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_requires_permission_or_valid_signed_token(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
            CmsBlockDefinitionSeeder::class,
        ]);

        $admin = User::factory()->create();
        $adminRole = Role::query()->where('name', CoreRole::Admin->value)->firstOrFail();
        $admin->assignRole($adminRole);

        $workflow = app(CmsWorkflowService::class);

        $page = $workflow->createPageWithDraft(
            actor: $admin,
            pageAttributes: ['page_type' => 'custom'],
            versionAttributes: [
                'title' => 'Preview Access',
                'slug' => 'preview-access',
                'path' => '/preview-access',
            ],
            blocks: [
                [
                    'block_type' => 'hero',
                    'data' => ['title' => 'Preview Access'],
                ],
            ],
        );

        $version = $page->currentDraftVersion()->firstOrFail();

        $this->get(route('cms.preview.show', $version))->assertForbidden();

        $token = app(PreviewTokenService::class)->issue($version, $admin);

        $this->get(route('cms.preview.show', ['version' => $version, 'token' => $token]))
            ->assertOk()
            ->assertSee('Preview mode active', false)
            ->assertSee('noindex,nofollow', false);

        $this->actingAs($admin)
            ->get(route('cms.preview.show', $version))
            ->assertOk();
    }
}
