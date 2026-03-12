<?php

use App\Modules\ClientRequests\Enums\ProjectRequestAttachmentScanStatus;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 120);
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_terminal')->default(false)->index();
            $table->string('system_status', 80)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('badge_tone', 30)->nullable();
            $table->boolean('visible_to_requester')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = CarbonImmutable::now();

        DB::table('request_statuses')->insert([
            [
                'code' => ProjectRequestSystemStatus::Submitted->value,
                'name' => 'Submitted',
                'is_system' => true,
                'is_default' => true,
                'is_terminal' => false,
                'system_status' => ProjectRequestSystemStatus::Submitted->value,
                'sort_order' => 10,
                'badge_tone' => 'info',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::UnderReview->value,
                'name' => 'Under Review',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => false,
                'system_status' => ProjectRequestSystemStatus::UnderReview->value,
                'sort_order' => 20,
                'badge_tone' => 'warning',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::NeedMoreInfo->value,
                'name' => 'Need More Info',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => false,
                'system_status' => ProjectRequestSystemStatus::NeedMoreInfo->value,
                'sort_order' => 30,
                'badge_tone' => 'warning',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::Quoted->value,
                'name' => 'Quoted',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => false,
                'system_status' => ProjectRequestSystemStatus::Quoted->value,
                'sort_order' => 40,
                'badge_tone' => 'info',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::Approved->value,
                'name' => 'Approved',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => false,
                'system_status' => ProjectRequestSystemStatus::Approved->value,
                'sort_order' => 50,
                'badge_tone' => 'success',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::InProgress->value,
                'name' => 'In Progress',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => false,
                'system_status' => ProjectRequestSystemStatus::InProgress->value,
                'sort_order' => 60,
                'badge_tone' => 'info',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::Completed->value,
                'name' => 'Completed',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => true,
                'system_status' => ProjectRequestSystemStatus::Completed->value,
                'sort_order' => 70,
                'badge_tone' => 'success',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => ProjectRequestSystemStatus::Rejected->value,
                'name' => 'Rejected',
                'is_system' => true,
                'is_default' => false,
                'is_terminal' => true,
                'system_status' => ProjectRequestSystemStatus::Rejected->value,
                'sort_order' => 80,
                'badge_tone' => 'danger',
                'visible_to_requester' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::create('project_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_status_id')->constrained('request_statuses')->restrictOnDelete();
            $table->string('requester_name', 160);
            $table->string('company_name', 180)->nullable();
            $table->string('contact_email', 190);
            $table->string('contact_phone', 50)->nullable();
            $table->string('project_title', 190);
            $table->string('project_summary', 500);
            $table->text('project_description');
            $table->decimal('budget', 12, 2)->nullable();
            $table->date('deadline')->nullable()->index();
            $table->string('project_type', 80)->index();
            $table->json('requested_features')->nullable();
            $table->json('preferred_tech_stack')->nullable();
            $table->json('preferred_meeting_availability')->nullable();
            $table->timestampTz('submitted_at')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'submitted_at']);
        });

        Schema::create('project_request_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_request_id')->constrained('project_requests')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('body');
            $table->string('visibility_type', 40)->default(ProjectRequestCommentVisibility::Internal->value)->index();
            $table->boolean('is_system_generated')->default(false)->index();
            $table->foreignId('edited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('edited_at')->nullable()->index();
            $table->foreignId('visibility_changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('visibility_changed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['project_request_id', 'visibility_type']);
        });

        Schema::create('project_request_attachments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_request_id')->constrained('project_requests')->cascadeOnDelete();
            $table->foreignUlid('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 40)->index();
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('disk', 50)->index();
            $table->string('directory', 255)->nullable();
            $table->string('path', 500);
            $table->string('mime_type', 160)->nullable();
            $table->string('extension', 30)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->char('checksum_sha256', 64)->nullable();
            $table->string('malware_scan_status', 40)->default(ProjectRequestAttachmentScanStatus::Pending->value)->index();
            $table->json('malware_scan_meta')->nullable();
            $table->boolean('visible_to_requester')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_request_id', 'category']);
        });

        Schema::create('project_request_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_request_id')->constrained('project_requests')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80)->index();
            $table->string('visibility_type', 40)->default(ProjectRequestCommentVisibility::RequesterVisible->value)->index();
            $table->string('title', 190);
            $table->text('body')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestamps();

            $table->index(['project_request_id', 'visibility_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_request_events');
        Schema::dropIfExists('project_request_attachments');
        Schema::dropIfExists('project_request_comments');
        Schema::dropIfExists('project_requests');
        Schema::dropIfExists('request_statuses');
    }
};
