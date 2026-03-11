<?php

use App\Enums\ApprovalState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_state', 40)->default(ApprovalState::PendingReview->value)->index();
            $table->timestampTz('submitted_at')->nullable()->index();
            $table->timestampTz('reviewed_at')->nullable()->index();
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['approvable_type', 'approvable_id', 'approval_state']);
        });

        Schema::create('status_histories', function (Blueprint $table): void {
            $table->id();
            $table->morphs('statusable');
            $table->string('from_state', 40)->nullable();
            $table->string('to_state', 40)->index();
            $table->string('visibility', 40)->nullable()->index();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('changed_at')->index();
            $table->timestamps();

            $table->index(['statusable_type', 'statusable_id', 'to_state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
        Schema::dropIfExists('approval_requests');
    }
};
