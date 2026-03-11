<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_login_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('reason', 80)->default('invalid_credentials')->index();
            $table->timestampTz('occurred_at')->index();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['email', 'ip_address', 'occurred_at']);
        });

        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_label')->nullable();
            $table->char('device_fingerprint', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestampTz('first_seen_at')->index();
            $table->timestampTz('last_seen_at')->index();
            $table->timestampTz('last_login_at')->nullable()->index();
            $table->boolean('is_trusted')->default(false);
            $table->string('last_seen_country', 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_fingerprint']);
            $table->index(['user_id', 'last_seen_at']);
        });

        Schema::create('user_session_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_identifier', 191)->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->foreignId('device_id')->nullable()->constrained('user_devices')->nullOnDelete();
            $table->timestampTz('last_activity_at')->index();
            $table->timestampTz('logged_in_at')->index();
            $table->timestampTz('logged_out_at')->nullable()->index();
            $table->timestampTz('invalidated_at')->nullable()->index();
            $table->boolean('is_current')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'logged_in_at']);
            $table->index(['user_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_session_histories');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('failed_login_attempts');
    }
};
