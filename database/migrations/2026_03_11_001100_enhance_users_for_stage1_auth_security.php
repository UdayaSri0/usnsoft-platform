<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 40)->nullable()->after('avatar_path');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 32)->default(AccountStatus::Active->value)->index()->after('phone');
            }

            if (! Schema::hasColumn('users', 'is_internal')) {
                $table->boolean('is_internal')->default(false)->index()->after('status');
            }

            if (! Schema::hasColumn('users', 'suspended_at')) {
                $table->timestampTz('suspended_at')->nullable()->index()->after('deactivated_at');
            }

            if (! Schema::hasColumn('users', 'deactivated_by')) {
                $table->foreignId('deactivated_by')->nullable()->after('deactivated_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'deactivation_reason')) {
                $table->text('deactivation_reason')->nullable()->after('deactivated_by');
            }

            if (! Schema::hasColumn('users', 'last_login_user_agent')) {
                $table->text('last_login_user_agent')->nullable()->after('last_login_ip');
            }

            if (! Schema::hasColumn('users', 'mfa_required_at')) {
                $table->timestampTz('mfa_required_at')->nullable()->index()->after('mfa_enabled_at');
            }
        });

        DB::table('users')
            ->whereNull('status')
            ->update(['status' => AccountStatus::Active->value]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'mfa_required_at')) {
                $table->dropColumn('mfa_required_at');
            }

            if (Schema::hasColumn('users', 'last_login_user_agent')) {
                $table->dropColumn('last_login_user_agent');
            }

            if (Schema::hasColumn('users', 'deactivation_reason')) {
                $table->dropColumn('deactivation_reason');
            }

            if (Schema::hasColumn('users', 'deactivated_by')) {
                $table->dropConstrainedForeignId('deactivated_by');
            }

            if (Schema::hasColumn('users', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }

            if (Schema::hasColumn('users', 'is_internal')) {
                $table->dropColumn('is_internal');
            }

            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('users', 'avatar_path')) {
                $table->dropColumn('avatar_path');
            }
        });
    }
};
