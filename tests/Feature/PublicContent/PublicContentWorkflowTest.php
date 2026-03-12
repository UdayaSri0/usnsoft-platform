<?php

namespace Tests\Feature\PublicContent;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
use App\Enums\VisibilityState;
use App\Modules\Blog\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PublicContent\Concerns\InteractsWithPublicContent;
use Tests\TestCase;

class PublicContentWorkflowTest extends TestCase
{
    use InteractsWithPublicContent;
    use RefreshDatabase;

    public function test_editor_can_create_blog_draft_but_cannot_publish_it(): void
    {
        $this->seedPublicContentCore();

        $editor = $this->makeUserWithRole(CoreRole::Editor);
        $category = $this->createBlogCategory($editor);

        $this->actingAs($editor)
            ->post(route('admin.blog.store'), [
                'blog_category_id' => $category->getKey(),
                'title' => 'Draft Editorial Update',
                'slug' => 'draft-editorial-update',
                'excerpt' => 'Draft excerpt.',
                'visibility' => VisibilityState::Public->value,
                'blocks' => $this->blogBlocks('<p>Draft editorial body.</p>'),
            ])
            ->assertRedirect();

        $post = BlogPost::query()->where('slug', 'draft-editorial-update')->firstOrFail();

        $this->assertSame(ContentWorkflowState::Draft, $post->workflow_state);
        $this->assertSame(ApprovalState::Draft, $post->approval_state);

        $this->actingAs($editor)
            ->post(route('admin.blog.versions.publish', ['post' => $post->getKey()]), [
                'preview_confirmed' => true,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $post->getMorphClass(),
            'auditable_id' => $post->getKey(),
            'event_type' => 'blog.post.created',
        ]);
    }

    public function test_admin_can_submit_job_for_review_but_superadmin_is_required_to_approve_and_publish(): void
    {
        $this->seedPublicContentCore();

        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);

        $job = $this->createJob($admin, [
            'title' => 'Systems Engineer',
            'slug' => 'systems-engineer',
            'workflow_state' => ContentWorkflowState::Draft->value,
            'approval_state' => ApprovalState::Draft->value,
            'approved_by' => null,
            'approved_at' => null,
            'published_by' => null,
            'published_at' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.careers.submit-review', ['job' => $job->getKey()]), [
                'notes' => 'Ready for review.',
            ])
            ->assertRedirect();

        $job->refresh();

        $this->assertSame(ContentWorkflowState::InReview, $job->workflow_state);
        $this->assertSame(ApprovalState::PendingReview, $job->approval_state);

        $this->actingAs($admin)
            ->post(route('admin.careers.versions.approve', ['job' => $job->getKey()]), [
                'notes' => 'Attempting self approval.',
            ])
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('admin.careers.versions.approve', ['job' => $job->getKey()]), [
                'notes' => 'Approved by super admin.',
            ])
            ->assertRedirect();

        $job->refresh();

        $this->assertSame(ContentWorkflowState::Approved, $job->workflow_state);
        $this->assertSame(ApprovalState::Approved, $job->approval_state);

        $this->actingAs($admin)
            ->post(route('admin.careers.versions.publish', ['job' => $job->getKey()]))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->post(route('admin.careers.versions.publish', ['job' => $job->getKey()]), [
                'notes' => 'Publish approved job.',
            ])
            ->assertRedirect();

        $job->refresh();

        $this->assertSame(ContentWorkflowState::Published, $job->workflow_state);
        $this->assertSame(ApprovalState::Approved, $job->approval_state);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $job->getMorphClass(),
            'auditable_id' => $job->getKey(),
            'event_type' => 'workflow.state_transitioned',
        ]);
    }
}
