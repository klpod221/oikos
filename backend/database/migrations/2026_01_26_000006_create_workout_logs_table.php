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
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_id')->nullable()->constrained()->nullOnDelete();

            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Actual duration

            $table->decimal('calories_burned', 10, 2)->default(0);

            $table->json('exercises_completed')->nullable();
            /* [{"exercise_id": 1, "actual_value": 20, "calories": 50}, ...] */

            $table->text('notes')->nullable();

            $table->timestamps();

            // Index for querying user workout history
            $table->index(['user_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};
