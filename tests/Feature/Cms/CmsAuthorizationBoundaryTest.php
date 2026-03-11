<?php

namespace Tests\Feature\Cms;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Services\CmsWorkflowService;
use Database\Seeders\CmsBlockDefinitionSeeder;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsAuthorizationBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_submit_for_review_but_cannot_approve_or_publish(): void
    {
        $this->seedCmsCore();

        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);

        $workflow = app(CmsWorkflowService::class);

        $page = $workflow->createPageWithDraft(
            actor: $admin,
            pageAttributes: [
                'page_type' => 'custom',
            ],
            versionAttributes: [
                'title' => 'Compliance Services',
                'slug' => 'compliance-services',
                'path' => '/compliance-services',
            ],
            blocks: [
                [
                    'block_type' => 'hero',
                    'data' => ['title' => 'Compliance Services'],
                ],
            ],
        );

        $draft = $page->currentDraftVersion()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.cms.pages.submit-review', $page), [
                'notes' => 'Please review this draft.',
            ])
            ->assertRedirect(route('admin.cms.pages.edit', $page));

        $draft->refresh();

        $this->assertSame('in_review', $draft->workflow_state->value);

        $this->actingAs($admin)
            ->post(route('admin.cms.versions.approve', $draft), [
                'notes' => 'I should not be able to approve this.',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.cms.versions.publish', $draft), [
                'preview_confirmed' => true,
            ])
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('admin.cms.versions.approve', $draft), [
                'notes' => 'Approved by super admin.',
            ])
            ->assertRedirect();

        $draft->refresh();

        $this->assertSame('approved', $draft->workflow_state->value);

        $this->actingAs($superAdmin)
            ->post(route('admin.cms.versions.publish', $draft), [
                'preview_confirmed' => true,
                'notes' => 'Publish now.',
            ])
            ->assertRedirect();

        $draft->refresh();

        $this->assertSame('published', $draft->workflow_state->value);
    }

    public function test_editor_cannot_use_advanced_blocks_without_permission(): void
    {
        $this->seedCmsCore();

        $editor = $this->makeUserWithRole(CoreRole::Editor);
        $workflow = app(CmsWorkflowService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $workflow->createPageWithDraft(
            actor: $editor,
            pageAttributes: [
                'page_type' => 'custom',
            ],
            versionAttributes: [
                'title' => 'Advanced Layout Test',
                'slug' => 'advanced-layout-test',
                'path' => '/advanced-layout-test',
            ],
            blocks: [
                [
                    'block_type' => 'slider',
                    'data' => [
                        'slides' => [
                            [
                                'title' => 'Slide 1',
                            ],
                        ],
                    ],
                ],
            ],
        );
    }

    public function test_developer_can_use_advanced_blocks_with_permission(): void
    {
        $this->seedCmsCore();

        $developer = $this->makeUserWithRole(CoreRole::Developer);
        $workflow = app(CmsWorkflowService::class);

        $page = $workflow->createPageWithDraft(
            actor: $developer,
            pageAttributes: [
                'page_type' => 'custom',
            ],
            versionAttributes: [
                'title' => 'Advanced Layout Allowed',
                'slug' => 'advanced-layout-allowed',
                'path' => '/advanced-layout-allowed',
            ],
            blocks: [
                [
                    'block_type' => 'slider',
                    'data' => [
                        'slides' => [
                            [
                                'title' => 'Slide 1',
                            ],
                        ],
                    ],
                ],
            ],
        );

        $draft = PageVersion::query()->findOrFail($page->current_draft_version_id);

        $this->assertCount(1, $draft->blocks);
        $this->assertSame('slider', $draft->blocks->first()?->blockDefinition?->key);
    }

    private function seedCmsCore(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
            CmsBlockDefinitionSeeder::class,
        ]);
    }

    private function makeUserWithRole(CoreRole $role): User
    {
        $user = User::factory()->create();
        $roleModel = Role::query()->where('name', $role->value)->firstOrFail();
        $user->assignRole($roleModel);

        return $user;
    }
}
