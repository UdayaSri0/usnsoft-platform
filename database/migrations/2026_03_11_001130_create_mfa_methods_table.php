<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mfa_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('method_type', 40)->index();
            $table->text('secret_encrypted')->nullable();
            $table->text('recovery_codes_encrypted')->nullable();
            $table->timestampTz('enabled_at')->nullable()->index();
            $table->timestampTz('required_at')->nullable()->index();
            $table->timestampTz('last_verified_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'method_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa_methods');
    }
};
