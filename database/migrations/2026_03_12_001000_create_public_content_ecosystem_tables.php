<?php

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\FileScanStatus;
use App\Enums\VisibilityState;
use App\Modules\Careers\Enums\JobApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addWorkflowColumns = function (Blueprint $table): void {
            $table->string('visibility', 30)->default(VisibilityState::Public->value)->index();
            $table->string('workflow_state', 40)->default(ContentWorkflowState::Draft->value)->index();
            $table->string('approval_state', 40)->default(ApprovalState::Draft->value)->index();
            $table->text('change_notes')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('submitted_at')->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable()->index();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('published_at')->nullable()->index();
            $table->timestampTz('scheduled_publish_at')->nullable()->index();
            $table->timestampTz('scheduled_unpublish_at')->nullable()->index();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('archived_at')->nullable()->index();
            $table->foreignId('preview_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('preview_confirmed_at')->nullable()->index();
        };

        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_posts', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->foreignUlid('featured_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->json('content_blocks_json')->nullable();
            $table->boolean('featured_flag')->default(false)->index();
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['blog_category_id', 'workflow_state']);
            $table->index(['featured_flag', 'published_at']);
        });

        Schema::create('blog_post_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_post_id', 'blog_tag_id']);
        });

        Schema::create('blog_post_related', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('related_blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_post_id', 'related_blog_post_id']);
        });

        Schema::create('faq_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('faqs', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->foreignId('faq_category_id')->nullable()->constrained('faq_categories')->nullOnDelete();
            $table->foreignId('linked_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('featured_flag')->default(false)->index();
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['faq_category_id', 'sort_order']);
            $table->index(['linked_product_id', 'workflow_state']);
        });

        Schema::create('jobs', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('location')->nullable();
            $table->string('employment_type', 80)->nullable()->index();
            $table->string('department', 120)->nullable()->index();
            $table->string('level', 120)->nullable()->index();
            $table->timestampTz('deadline')->nullable()->index();
            $table->boolean('featured_flag')->default(false)->index();
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department', 'level']);
        });

        Schema::create('job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('cover_message')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('status', 40)->default(JobApplicationStatus::Submitted->value)->index();
            $table->foreignId('last_status_changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('submitted_at')->nullable()->index();
            $table->timestampTz('reviewed_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['job_id', 'status']);
            $table->index(['email', 'submitted_at']);
        });

        Schema::create('job_application_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->foreignUlid('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('file_type', 50)->index();
            $table->string('original_name');
            $table->string('path');
            $table->string('disk', 50)->index();
            $table->string('mime_type', 160)->nullable();
            $table->string('extension', 30)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->char('checksum_sha256', 64)->nullable();
            $table->string('malware_scan_status', 30)->default(FileScanStatus::Pending->value)->index();
            $table->json('malware_scan_meta')->nullable();
            $table->timestampTz('uploaded_at')->nullable()->index();
            $table->timestamps();

            $table->index(['job_application_id', 'file_type']);
        });

        Schema::create('job_application_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note_body');
            $table->boolean('is_internal')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->string('client_name');
            $table->string('company_name')->nullable();
            $table->string('role_title')->nullable();
            $table->text('quote');
            $table->foreignUlid('avatar_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('featured_flag')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partners', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->foreignUlid('logo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('website_url')->nullable();
            $table->text('summary')->nullable();
            $table->string('category')->nullable()->index();
            $table->boolean('featured_flag')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_members', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->string('full_name');
            $table->string('slug')->unique();
            $table->string('role_title');
            $table->text('short_bio')->nullable();
            $table->longText('full_bio')->nullable();
            $table->foreignUlid('photo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('public_email')->nullable();
            $table->string('public_phone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('featured_flag')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('timeline_entries', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->timestampTz('event_date')->nullable()->index();
            $table->string('date_label', 80)->nullable();
            $table->foreignUlid('image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->boolean('featured_flag')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('achievements', function (Blueprint $table) use ($addWorkflowColumns): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->timestampTz('achievement_date')->nullable()->index();
            $table->foreignUlid('image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('metric_value', 120)->nullable();
            $table->string('metric_prefix', 20)->nullable();
            $table->string('metric_suffix', 20)->nullable();
            $table->string('category', 120)->nullable()->index();
            $table->boolean('featured_flag')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $addWorkflowColumns($table);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('timeline_entries');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('job_application_notes');
        Schema::dropIfExists('job_application_files');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('blog_post_related');
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
