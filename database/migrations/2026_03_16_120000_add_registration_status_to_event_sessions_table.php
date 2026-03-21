<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_sessions', function (Blueprint $table) {
            $table->string('registration_status', 32)->default('open')->after('capacity_reserved');
        });

        // Migrate existing data: 
        // - active status + not blocked -> open
        // - active status + blocked -> paused
        // - inactive status -> hidden
        \Illuminate\Support\Facades\DB::statement("
            UPDATE event_sessions 
            SET registration_status = CASE 
                WHEN status = 'active' AND (is_registration_blocked IS NULL OR is_registration_blocked = 0) THEN 'open'
                WHEN status = 'active' AND is_registration_blocked = 1 THEN 'paused'
                ELSE 'hidden'
            END
        ");

        Schema::table('event_sessions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('is_registration_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('event_sessions', function (Blueprint $table) {
            $table->string('status', 32)->default('active');
            $table->boolean('is_registration_blocked')->default(false);
        });

        // Rollback: convert registration_status back to status/is_registration_blocked
        \Illuminate\Support\Facades\DB::statement("
            UPDATE event_sessions 
            SET status = CASE 
                WHEN registration_status = 'hidden' THEN 'inactive'
                ELSE 'active'
            END,
            is_registration_blocked = CASE 
                WHEN registration_status = 'paused' THEN 1
                ELSE 0
            END
        ");

        Schema::table('event_sessions', function (Blueprint $table) {
            $table->dropColumn('registration_status');
        });
    }
};
