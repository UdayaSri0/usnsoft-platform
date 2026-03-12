<?php

namespace Tests\Feature\PublicContent;

use App\Enums\CoreRole;
use App\Modules\Blog\Models\BlogPost;
use App\Services\Content\DirectContentWorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PublicContent\Concerns\InteractsWithPublicContent;
use Tests\TestCase;

class BlogPublicVisibilityTest extends TestCase
{
    use InteractsWithPublicContent;
    use RefreshDatabase;

    public function test_scheduled_blog_posts_remain_private_until_schedule_processor_runs(): void
    {
        $this->seedPublicContentCore();

        $editor = $this->makeUserWithRole(CoreRole::Editor);
        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $post = $this->createDraftBlogPost($editor, [
            'title' => 'Scheduled Launch Note',
            'slug' => 'scheduled-launch-note',
        ]);

        $workflow = app(DirectContentWorkflowService::class);
        $workflow->submitForReview($post, $editor, 'Ready for approval.');

        $post->refresh();
        $workflow->approve($post, $superAdmin, 'Approved for schedule.');

        $scheduleAt = CarbonImmutable::now()->addMinutes(10);
        $workflow->schedulePublish($post->fresh(), $superAdmin, $scheduleAt, 'Schedule publish.');

        $this->get(route('blog.show', ['post' => $post->slug]))->assertNotFound();

        $results = $workflow->processScheduledTransitions(BlogPost::class, false, $scheduleAt->addMinute());

        $this->assertSame(1, $results['published']);

        $post->refresh();

        $this->assertSame('published', $post->workflow_state->value);
        $this->assertNotNull($post->published_at);

        $this->get(route('blog.show', ['post' => $post->slug]))
            ->assertOk()
            ->assertSee('Scheduled Launch Note');
    }

    public function test_blog_index_filters_published_posts_by_category_and_tag(): void
    {
        $this->seedPublicContentCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);

        $security = $this->createBlogCategory($superAdmin, ['name' => 'Security', 'slug' => 'security']);
        $platform = $this->createBlogCategory($superAdmin, ['name' => 'Platform', 'slug' => 'platform']);
        $hardening = $this->createBlogTag($superAdmin, ['name' => 'Hardening', 'slug' => 'hardening']);
        $operations = $this->createBlogTag($superAdmin, ['name' => 'Operations', 'slug' => 'operations']);

        $matchingPost = $this->createDraftBlogPost($superAdmin, [
            'title' => 'Security Hardening Playbook',
            'slug' => 'security-hardening-playbook',
            'category' => $security,
            'tag' => $hardening,
        ]);
        $this->publishBlogPost($matchingPost, $superAdmin);

        $otherPost = $this->createDraftBlogPost($superAdmin, [
            'title' => 'Platform Operations Notes',
            'slug' => 'platform-operations-notes',
            'category' => $platform,
            'tag' => $operations,
        ]);
        $this->publishBlogPost($otherPost, $superAdmin);

        $this->get(route('blog.index', ['category' => 'security', 'tag' => 'hardening']))
            ->assertOk()
            ->assertSee('Security Hardening Playbook')
            ->assertDontSee('Platform Operations Notes');
    }
}
