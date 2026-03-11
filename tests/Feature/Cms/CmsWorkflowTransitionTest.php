<?php

namespace Tests\Feature\Cms;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\Pages\Services\CmsWorkflowService;
use Carbon\CarbonImmutable;
use Database\Seeders\CmsBlockDefinitionSeeder;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CmsWorkflowTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_version_can_move_through_review_approve_with_publish_guard_and_archive(): void
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
                'title' => 'Security Services',
                'slug' => 'security-services',
                'path' => '/security-services',
            ],
            blocks: [
                [
                    'block_type' => 'hero',
                    'data' => [
                        'title' => 'Security Services',
                    ],
                ],
            ],
        );

        $draft = $page->currentDraftVersion()->firstOrFail();

        $this->assertSame(ContentWorkflowState::Draft, $draft->workflow_state);
        $this->assertSame(ApprovalState::Draft, $draft->approval_state);

        $workflow->submitForReview($draft, $admin, 'Submit for compliance review');
        $draft->refresh();

        $this->assertSame(ContentWorkflowState::InReview, $draft->workflow_state);
        $this->assertSame(ApprovalState::PendingReview, $draft->approval_state);

        $workflow->approve($draft, $superAdmin, 'Approved');
        $draft->refresh();

        $this->assertSame(ContentWorkflowState::Approved, $draft->workflow_state);
        $this->assertSame(ApprovalState::Approved, $draft->approval_state);

        try {
            $workflow->publishNow($draft, $superAdmin);
            $this->fail('Publishing should fail when preview is not confirmed.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Preview confirmation is required before publishing.', $exception->getMessage());
        }

        $workflow->confirmPreview($draft, $superAdmin);
        $workflow->publishNow($draft, $superAdmin);

        $draft->refresh();
        $page->refresh();

        $this->assertSame(ContentWorkflowState::Published, $draft->workflow_state);
        $this->assertSame($draft->getKey(), $page->current_published_version_id);

        $workflow->archive($draft, $superAdmin);

        $draft->refresh();
        $page->refresh();

        $this->assertSame(ContentWorkflowState::Archived, $draft->workflow_state);
        $this->assertNull($page->current_published_version_id);
    }

    public function test_scheduled_publish_and_unpublish_transitions_are_processed(): void
    {
        $this->seedCmsCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $workflow = app(CmsWorkflowService::class);

        $page = $workflow->createPageWithDraft(
            actor: $superAdmin,
            pageAttributes: [
                'page_type' => 'custom',
            ],
            versionAttributes: [
                'title' => 'Network Operations',
                'slug' => 'network-operations',
                'path' => '/network-operations',
            ],
            blocks: [
                [
                    'block_type' => 'hero',
                    'data' => [
                        'title' => 'Network Operations',
                    ],
                ],
            ],
        );

        $draft = $page->currentDraftVersion()->firstOrFail();

        $workflow->submitForReview($draft, $superAdmin);
        $workflow->approve($draft, $superAdmin);
        $workflow->confirmPreview($draft, $superAdmin);

        $publishAt = CarbonImmutable::now()->addMinutes(5);
        $unpublishAt = CarbonImmutable::now()->addMinutes(10);

        $workflow->schedulePublish(
            version: $draft,
            actor: $superAdmin,
            publishAt: $publishAt,
            unpublishAt: $unpublishAt,
        );

        $draft->refresh();

        $this->assertSame(ContentWorkflowState::Scheduled, $draft->workflow_state);
        $this->assertSame($publishAt->timestamp, $draft->scheduled_publish_at?->timestamp);
        $this->assertSame($unpublishAt->timestamp, $draft->scheduled_unpublish_at?->timestamp);

        $publishResult = $workflow->processScheduledTransitions($publishAt->addMinute());

        $this->assertSame(['published' => 1, 'archived' => 0], $publishResult);

        $draft->refresh();

        $this->assertSame(ContentWorkflowState::Published, $draft->workflow_state);

        $archiveResult = $workflow->processScheduledTransitions($unpublishAt->addMinute());

        $this->assertSame(['published' => 0, 'archived' => 1], $archiveResult);

        $draft->refresh();

        $this->assertSame(ContentWorkflowState::Archived, $draft->workflow_state);
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
