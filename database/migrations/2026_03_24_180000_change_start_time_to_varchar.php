<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE template_slots ALTER COLUMN start_time TYPE varchar(10)');
        } else {
            Schema::table('template_slots', function (Blueprint $table) {
                $table->string('start_time', 10)->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE template_slots ALTER COLUMN start_time TYPE time');
        }
    }
};
