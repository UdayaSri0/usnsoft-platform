<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_deletion_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('account_deletion_requests', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('reason');
            }
        });

        DB::table('account_deletion_requests')
            ->where('status', 'requested')
            ->update(['status' => 'pending']);

        Schema::table('audit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('audit_logs', 'event')) {
                $table->string('event')->nullable()->after('event_type')->index();
            }

            if (! Schema::hasColumn('audit_logs', 'created_at')) {
                $table->timestampTz('created_at')->nullable()->index()->after('occurred_at');
            }
        });

        DB::table('audit_logs')
            ->whereNull('event')
            ->update(['event' => DB::raw('event_type')]);

        DB::table('audit_logs')
            ->whereNull('created_at')
            ->update(['created_at' => DB::raw('occurred_at')]);
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('audit_logs', 'created_at')) {
                $table->dropColumn('created_at');
            }

            if (Schema::hasColumn('audit_logs', 'event')) {
                $table->dropColumn('event');
            }
        });

        Schema::table('account_deletion_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('account_deletion_requests', 'internal_notes')) {
                $table->dropColumn('internal_notes');
            }
        });

        DB::table('account_deletion_requests')
            ->where('status', 'pending')
            ->update(['status' => 'requested']);
    }
};
