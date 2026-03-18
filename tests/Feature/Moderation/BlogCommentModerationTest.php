<?php

namespace Tests\Feature\Moderation;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PublicContent\Concerns\InteractsWithPublicContent;
use Tests\TestCase;

class BlogCommentModerationTest extends TestCase
{
    use InteractsWithPublicContent;
    use RefreshDatabase;

    public function test_pending_comments_are_not_public_but_approved_comments_are(): void
    {
        $this->seedPublicContentCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $commenter = $this->makeUserWithRole(CoreRole::User);
        $post = $this->publishBlogPost($this->createDraftBlogPost($superAdmin, [
            'title' => 'Moderated Blog Post',
            'slug' => 'moderated-blog-post',
        ]), $superAdmin);

        $pending = $this->createComment($post, $commenter, CommentStatus::Pending, 'Pending comment body');
        $approved = $this->createComment($post, $commenter, CommentStatus::Approved, 'Approved comment body', $superAdmin, 'Internal note');

        $this->get(route('blog.show', ['post' => $post->slug]))
            ->assertOk()
            ->assertDontSee($pending->body)
            ->assertSee($approved->body)
            ->assertDontSee('Internal note');
    }

    public function test_verified_user_can_submit_comment_and_it_starts_pending(): void
    {
        $this->seedPublicContentCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $post = $this->publishBlogPost($this->createDraftBlogPost($superAdmin, [
            'title' => 'Comment Intake Post',
            'slug' => 'comment-intake-post',
        ]), $superAdmin);

        $this->actingAs($user)
            ->post(route('blog.comments.store', ['post' => $post->slug]), [
                'body' => 'This comment should wait for moderation.',
            ])
            ->assertRedirect(route('blog.show', ['post' => $post->slug]));

        $this->assertDatabaseHas('comments', [
            'commentable_type' => $post->getMorphClass(),
            'commentable_id' => $post->getKey(),
            'user_id' => $user->getKey(),
            'status' => CommentStatus::Pending->value,
        ]);
    }

    public function test_authorized_staff_can_moderate_comments_and_audit_is_written(): void
    {
        $this->seedPublicContentCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $commenter = $this->makeUserWithRole(CoreRole::User);
        $post = $this->publishBlogPost($this->createDraftBlogPost($superAdmin, [
            'title' => 'Comment Queue Post',
            'slug' => 'comment-queue-post',
        ]), $superAdmin);
        $comment = $this->createComment($post, $commenter, CommentStatus::Pending, 'Moderate me');

        $this->actingAs($admin)
            ->put(route('admin.comments.moderate', ['comment' => $comment->getKey()]), [
                'status' => CommentStatus::Approved->value,
                'moderation_reason' => 'Approved for publication.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->getKey(),
            'status' => CommentStatus::Approved->value,
            'moderated_by' => $admin->getKey(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'comments.moderated',
            'action' => 'moderate_comment',
            'auditable_type' => 'comment',
            'auditable_id' => $comment->getKey(),
            'actor_id' => $admin->getKey(),
        ]);
    }

    public function test_unauthorized_user_cannot_moderate_comments(): void
    {
        $this->seedPublicContentCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $user = $this->makeUserWithRole(CoreRole::User);
        $commenter = $this->makeUserWithRole(CoreRole::User);
        $post = $this->publishBlogPost($this->createDraftBlogPost($superAdmin, [
            'title' => 'Protected Queue Post',
            'slug' => 'protected-queue-post',
        ]), $superAdmin);
        $comment = $this->createComment($post, $commenter, CommentStatus::Pending, 'Blocked moderation attempt');

        $this->actingAs($user)
            ->put(route('admin.comments.moderate', ['comment' => $comment->getKey()]), [
                'status' => CommentStatus::Approved->value,
            ])
            ->assertForbidden();
    }

    private function createComment(BlogPost $post, User $author, CommentStatus $status, string $body, ?User $moderator = null, ?string $reason = null): Comment
    {
        return $post->comments()->create([
            'user_id' => $author->getKey(),
            'body' => $body,
            'status' => $status,
            'submitted_at' => CarbonImmutable::now()->subHour(),
            'approved_at' => $status->isPubliclyVisible() ? CarbonImmutable::now()->subMinutes(30) : null,
            'moderated_at' => $status === CommentStatus::Pending ? null : CarbonImmutable::now()->subMinutes(30),
            'moderated_by' => $moderator?->getKey(),
            'moderation_reason' => $reason,
        ]);
    }
}
