<?php

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Modules\Pages\Enums\BlockEditorMode;
use App\Modules\Pages\Enums\PageType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->nullable()->unique();
            $table->string('page_type', 30)->default(PageType::Custom->value)->index();
            $table->string('title_current');
            $table->string('slug_current');
            $table->string('path_current')->unique();
            $table->boolean('is_home')->default(false)->index();
            $table->boolean('is_system_page')->default(false)->index();
            $table->boolean('is_locked_slug')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('current_draft_version_id')->nullable();
            $table->unsignedBigInteger('current_published_version_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_type', 'is_active']);
            $table->index(['is_home', 'is_system_page']);
        });

        Schema::create('block_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category', 60)->index();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->json('schema_json')->nullable();
            $table->json('default_data_json')->nullable();
            $table->json('default_layout_json')->nullable();
            $table->string('editor_mode', 40)->default(BlockEditorMode::Basic->value)->index();
            $table->boolean('is_reusable_allowed')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('rendering_view');
            $table->string('rendering_component_class')->nullable();
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        Schema::create('reusable_blocks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('block_definition_id')->constrained('block_definitions')->cascadeOnDelete();
            $table->string('workflow_state', 40)->default(ContentWorkflowState::Draft->value)->index();
            $table->string('approval_state', 40)->default(ApprovalState::Draft->value)->index();
            $table->json('data_json');
            $table->json('layout_json')->nullable();
            $table->json('visibility_json')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workflow_state', 'approval_state']);
        });

        Schema::create('page_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->string('slug');
            $table->string('path');
            $table->text('summary')->nullable();
            $table->string('workflow_state', 40)->default(ContentWorkflowState::Draft->value)->index();
            $table->string('approval_state', 40)->default(ApprovalState::Draft->value)->index();
            $table->text('change_notes')->nullable();
            $table->json('seo_snapshot_json')->nullable();
            $table->json('layout_settings_json')->nullable();
            $table->char('preview_token_hash', 64)->nullable()->index();
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
            $table->foreignId('based_on_version_id')->nullable()->constrained('page_versions')->nullOnDelete();
            $table->foreignId('preview_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('preview_confirmed_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['page_id', 'version_number']);
            $table->index(['page_id', 'workflow_state']);
            $table->index(['page_id', 'approval_state']);
            $table->index(['workflow_state', 'scheduled_publish_at']);
        });

        Schema::create('page_version_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_version_id')->constrained('page_versions')->cascadeOnDelete();
            $table->foreignId('block_definition_id')->constrained('block_definitions')->cascadeOnDelete();
            $table->foreignId('reusable_block_id')->nullable()->constrained('reusable_blocks')->nullOnDelete();
            $table->string('region_key', 80)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('internal_name', 160)->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->json('visibility_json')->nullable();
            $table->json('layout_json')->nullable();
            $table->json('data_json');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_version_id', 'region_key', 'sort_order']);
        });

        Schema::create('seo_meta', function (Blueprint $table): void {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->foreignUlid('og_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('schema_type', 80)->nullable();
            $table->json('extra_json')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
        });

        Schema::create('approval_records', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->string('action', 40)->index();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestampTz('created_at')->index();

            $table->index(['approvable_type', 'approvable_id', 'action']);
        });

        Schema::create('preview_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_version_id')->constrained('page_versions')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('last_accessed_at')->nullable()->index();
            $table->timestampTz('created_at')->index();
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->foreign('current_draft_version_id')
                ->references('id')
                ->on('page_versions')
                ->nullOnDelete();

            $table->foreign('current_published_version_id')
                ->references('id')
                ->on('page_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropForeign(['current_draft_version_id']);
            $table->dropForeign(['current_published_version_id']);
        });

        Schema::dropIfExists('preview_access_tokens');
        Schema::dropIfExists('approval_records');
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('page_version_blocks');
        Schema::dropIfExists('page_versions');
        Schema::dropIfExists('reusable_blocks');
        Schema::dropIfExists('block_definitions');
        Schema::dropIfExists('pages');
    }
};
