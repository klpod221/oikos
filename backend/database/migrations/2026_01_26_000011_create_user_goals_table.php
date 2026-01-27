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
        Schema::create('user_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->enum('goal_type', ['maintain', 'lose_weight', 'gain_muscle', 'improve_fitness']);

            $table->decimal('target_weight', 6, 2)->nullable(); // kg
            $table->integer('target_calories')->nullable(); // Daily kcal
            $table->decimal('target_protein', 6, 2)->nullable(); // g
            $table->decimal('target_carbs', 6, 2)->nullable(); // g
            $table->decimal('target_fat', 6, 2)->nullable(); // g

            $table->integer('weekly_workout_target')->default(3); // Times per week

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goals');
    }
};
