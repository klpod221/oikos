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
        Schema::create('workout_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable(); // "Morning Cardio", "Evening Strength"

            // Flexible Scheduling via JSON
            $table->json('schedule_config');
            /* Example JSON structures:
             * {"type": "weekly", "days": [1,3,5], "time": "07:00"} // Mon, Wed, Fri at 7 AM
             * {"type": "interval", "every_n_days": 3, "time": "18:00"} // Every 3 days at 6 PM
             * {"type": "specific_dates", "dates": ["2026-02-01", "2026-02-15"]} // Specific dates
             */

            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_schedules');
    }
};
