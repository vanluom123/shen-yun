<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('template_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_template_id')->constrained('session_templates')->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->unsigned();
            $table->time('time');
            $table->unsignedInteger('default_capacity');
            $table->timestamps();

            $table->index('session_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_slots');
    }
};
