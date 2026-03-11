<?php

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductKind;
use App\Modules\Products\Enums\ProductPricingMode;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Enums\ProductVerificationSource;
use App\Modules\Products\Enums\ProductVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
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

        Schema::create('product_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name_current');
            $table->string('slug_current')->unique();
            $table->text('short_description_current')->nullable();
            $table->string('product_kind', 40)->default(ProductKind::WebApp->value)->index();
            $table->string('visibility', 40)->default(ProductVisibility::Public->value)->index();
            $table->boolean('featured_flag')->default(false)->index();
            $table->string('current_version_label', 120)->nullable();
            $table->foreignUlid('featured_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->unsignedBigInteger('current_draft_version_id')->nullable();
            $table->unsignedBigInteger('current_published_version_id')->nullable();
            $table->unsignedInteger('approved_review_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['visibility', 'featured_flag']);
            $table->index(['product_kind', 'visibility']);
        });

        Schema::create('product_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('product_kind', 40)->default(ProductKind::WebApp->value)->index();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->longText('rich_body')->nullable();
            $table->boolean('featured_flag')->default(false)->index();
            $table->string('product_visibility', 40)->default(ProductVisibility::Public->value)->index();
            $table->string('download_visibility', 40)->default(ProductDownloadVisibility::Verified->value)->index();
            $table->string('pricing_mode', 40)->default(ProductPricingMode::Free->value)->index();
            $table->string('pricing_text')->nullable();
            $table->string('current_version', 120)->nullable();
            $table->longText('release_notes')->nullable();
            $table->longText('changelog')->nullable();
            $table->string('documentation_link')->nullable();
            $table->string('github_link')->nullable();
            $table->string('support_contact')->nullable();
            $table->string('video_url')->nullable();
            $table->foreignUlid('featured_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->boolean('release_notes_visible')->default(true);
            $table->boolean('changelog_visible')->default(true);
            $table->boolean('reviews_enabled')->default(true);
            $table->boolean('review_requires_verification')->default(true);
            $table->string('workflow_state', 40)->default(ContentWorkflowState::Draft->value)->index();
            $table->string('approval_state', 40)->default(ApprovalState::Draft->value)->index();
            $table->text('change_notes')->nullable();
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
            $table->foreignId('based_on_version_id')->nullable()->constrained('product_versions')->nullOnDelete();
            $table->foreignId('preview_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('preview_confirmed_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'version_number']);
            $table->index(['product_id', 'workflow_state']);
            $table->index(['product_id', 'approval_state']);
            $table->index(['product_visibility', 'workflow_state']);
        });

        Schema::create('product_version_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_version_id')->constrained('product_versions')->cascadeOnDelete();
            $table->foreignId('product_tag_id')->constrained('product_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_version_id', 'product_tag_id']);
        });

        Schema::create('product_version_platforms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_version_id')->constrained('product_versions')->cascadeOnDelete();
            $table->string('platform', 40)->index();
            $table->timestamps();

            $table->unique(['product_version_id', 'platform']);
        });

        Schema::create('product_version_related_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_version_id')->constrained('product_versions')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_version_id', 'related_product_id']);
        });

        Schema::create('product_version_faqs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_version_id')->constrained('product_versions')->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_version_id', 'sort_order']);
        });

        Schema::create('product_version_screenshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_version_id')->constrained('product_versions')->cascadeOnDelete();
            $table->foreignUlid('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_version_id', 'sort_order']);
        });

        Schema::create('product_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_version_id')->nullable()->constrained('product_versions')->cascadeOnDelete();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('version_label', 120)->nullable();
            $table->string('download_mode', 40)->default(ProductDownloadMode::ManualRequest->value)->index();
            $table->string('visibility', 40)->default(ProductDownloadVisibility::Verified->value)->index();
            $table->string('external_url')->nullable();
            $table->foreignUlid('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('review_eligible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_version_id', 'sort_order']);
        });

        Schema::create('product_download_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_version_id')->nullable()->constrained('product_versions')->nullOnDelete();
            $table->foreignId('product_download_id')->nullable()->constrained('product_downloads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('access_granted')->default(false)->index();
            $table->string('download_mode', 40)->default(ProductDownloadMode::ManualRequest->value)->index();
            $table->string('denied_reason', 160)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('attempted_at')->index();
            $table->timestampTz('completed_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('product_user_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('source', 40)->default(ProductVerificationSource::Download->value)->index();
            $table->foreignId('product_download_access_id')->nullable()->constrained('product_download_accesses')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->index();
            $table->timestampTz('expires_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'user_id', 'source'], 'product_user_verifications_unique_source');
        });

        Schema::create('product_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_user_verification_id')->nullable()->constrained('product_user_verifications')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('moderation_state', 40)->default(ProductReviewState::Pending->value)->index();
            $table->string('verification_source', 40)->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestampTz('submitted_at')->index();
            $table->timestampTz('moderated_at')->nullable()->index();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('moderation_notes')->nullable();
            $table->timestampTz('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'moderation_state']);
        });

        Schema::create('product_preview_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_version_id')->constrained('product_versions')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('last_accessed_at')->nullable()->index();
            $table->timestampTz('created_at')->index();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreign('current_draft_version_id')
                ->references('id')
                ->on('product_versions')
                ->nullOnDelete();

            $table->foreign('current_published_version_id')
                ->references('id')
                ->on('product_versions')
                ->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX product_reviews_unique_active_review ON product_reviews (product_id, user_id) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS product_reviews_unique_active_review');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['current_draft_version_id']);
            $table->dropForeign(['current_published_version_id']);
        });

        Schema::dropIfExists('product_preview_access_tokens');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_user_verifications');
        Schema::dropIfExists('product_download_accesses');
        Schema::dropIfExists('product_downloads');
        Schema::dropIfExists('product_version_screenshots');
        Schema::dropIfExists('product_version_faqs');
        Schema::dropIfExists('product_version_related_products');
        Schema::dropIfExists('product_version_platforms');
        Schema::dropIfExists('product_version_tags');
        Schema::dropIfExists('product_versions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('product_categories');
    }
};
