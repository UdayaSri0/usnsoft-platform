<?php

namespace Tests\Feature\PublicContent\Concerns;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
use App\Enums\VisibilityState;
use App\Models\User;
use App\Modules\Blog\Models\BlogCategory;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Blog\Models\BlogTag;
use App\Modules\Careers\Models\Job;
use App\Modules\Careers\Services\JobApplicationService;
use App\Modules\Faq\Models\Faq;
use App\Modules\Faq\Models\FaqCategory;
use App\Modules\IdentityAccess\Models\Role;
use Carbon\CarbonImmutable;
use Database\Seeders\CmsBlockDefinitionSeeder;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;

trait InteractsWithPublicContent
{
    protected function seedPublicContentCore(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
            CmsBlockDefinitionSeeder::class,
        ]);
    }

    protected function makeUserWithRole(CoreRole $role): User
    {
        $user = User::factory()->create();
        $roleModel = Role::query()->where('name', $role->value)->firstOrFail();
        $user->assignRole($roleModel);

        return $user;
    }

    protected function createBlogCategory(?User $actor = null, array $overrides = []): BlogCategory
    {
        return BlogCategory::query()->create(array_merge([
            'name' => 'Security',
            'slug' => 'security',
            'description' => 'Security updates',
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ], $overrides));
    }

    protected function createBlogTag(?User $actor = null, array $overrides = []): BlogTag
    {
        return BlogTag::query()->create(array_merge([
            'name' => 'Hardening',
            'slug' => 'hardening',
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ], $overrides));
    }

    protected function createDraftBlogPost(User $actor, array $overrides = []): BlogPost
    {
        $category = $overrides['category'] ?? $this->createBlogCategory($actor);
        $tag = $overrides['tag'] ?? null;

        $post = BlogPost::query()->create(array_merge([
            'blog_category_id' => $category->getKey(),
            'author_user_id' => $actor->getKey(),
            'title' => 'Approval Workflow Post',
            'slug' => 'approval-workflow-post',
            'excerpt' => 'Approval workflow excerpt',
            'content_blocks_json' => $this->blogBlocks(),
            'featured_flag' => false,
            'visibility' => VisibilityState::Public->value,
            'workflow_state' => ContentWorkflowState::Draft->value,
            'approval_state' => ApprovalState::Draft->value,
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ], collect($overrides)->except(['category', 'tag'])->all()));

        if ($tag instanceof BlogTag) {
            $post->tags()->sync([$tag->getKey()]);
        }

        return $post->fresh(['category', 'tags']);
    }

    protected function publishBlogPost(BlogPost $post, ?User $actor = null, array $overrides = []): BlogPost
    {
        $actor ??= $post->author ?? $this->makeUserWithRole(CoreRole::SuperAdmin);

        $post->forceFill(array_merge([
            'visibility' => VisibilityState::Public->value,
            'workflow_state' => ContentWorkflowState::Published->value,
            'approval_state' => ApprovalState::Approved->value,
            'approved_by' => $actor->getKey(),
            'approved_at' => CarbonImmutable::now()->subMinute(),
            'published_by' => $actor->getKey(),
            'published_at' => CarbonImmutable::now(),
            'updated_by' => $actor->getKey(),
        ], $overrides))->save();

        return $post->fresh(['category', 'tags', 'author']);
    }

    protected function createFaqCategory(?User $actor = null, array $overrides = []): FaqCategory
    {
        return FaqCategory::query()->create(array_merge([
            'name' => 'General',
            'slug' => 'general',
            'description' => 'General FAQ',
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ], $overrides));
    }

    protected function createFaq(User $actor, array $overrides = []): Faq
    {
        $category = $overrides['category'] ?? $this->createFaqCategory($actor);

        return Faq::query()->create(array_merge([
            'faq_category_id' => $category->getKey(),
            'question' => 'How does approval work?',
            'answer' => '<p>All public content requires review before publishing.</p>',
            'sort_order' => 0,
            'featured_flag' => false,
            'visibility' => VisibilityState::Public->value,
            'workflow_state' => ContentWorkflowState::Published->value,
            'approval_state' => ApprovalState::Approved->value,
            'approved_by' => $actor->getKey(),
            'approved_at' => CarbonImmutable::now()->subMinute(),
            'published_by' => $actor->getKey(),
            'published_at' => CarbonImmutable::now(),
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ], collect($overrides)->except(['category'])->all()));
    }

    protected function createJob(User $actor, array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'title' => 'Platform Engineer',
            'slug' => 'platform-engineer',
            'summary' => 'Build approval-aware platforms.',
            'description' => '<p>Work on Laravel, security, and operations.</p>',
            'location' => 'Remote',
            'employment_type' => 'Full-time',
            'department' => 'Engineering',
            'level' => 'Mid',
            'deadline' => CarbonImmutable::now()->addDays(10),
            'featured_flag' => false,
            'visibility' => VisibilityState::Public->value,
            'workflow_state' => ContentWorkflowState::Published->value,
            'approval_state' => ApprovalState::Approved->value,
            'approved_by' => $actor->getKey(),
            'approved_at' => CarbonImmutable::now()->subMinute(),
            'published_by' => $actor->getKey(),
            'published_at' => CarbonImmutable::now(),
            'created_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, UploadedFile|array<int, UploadedFile>|null>  $files
     */
    protected function submitApplication(Job $job, array $attributes = [], array $files = [])
    {
        return app(JobApplicationService::class)->submit(
            $job,
            array_merge([
                'full_name' => 'Jane Applicant',
                'email' => 'jane@example.test',
                'phone' => '555-0110',
                'cover_message' => 'Application message.',
            ], $attributes),
            array_merge([
                'cv' => UploadedFile::fake()->create('resume.pdf', 64, 'application/pdf'),
            ], $files),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function blogBlocks(string $html = '<p>Structured content body.</p>'): array
    {
        return [[
            'block_type' => 'rich_text',
            'region_key' => 'main',
            'sort_order' => 1,
            'internal_name' => 'Body',
            'is_enabled' => true,
            'visibility' => [],
            'layout' => [],
            'data' => [
                'content_html' => $html,
            ],
        ]];
    }
}
