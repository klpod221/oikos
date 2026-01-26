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
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('weight', 6, 2); // kg
            $table->decimal('height', 6, 2); // cm
            $table->integer('age');
            $table->enum('gender', ['male', 'female', 'other']);

            $table->enum('activity_level', [
                'sedentary',       // Little/no exercise
                'lightly_active',  // 1-3 days/week
                'moderately_active', // 3-5 days/week
                'very_active',     // 6-7 days/week
                'extra_active'     // Physical job + training
            ])->default('sedentary');

            $table->date('recorded_at'); // Allow historical tracking

            $table->timestamps();

            // Index for getting latest stats by date
            $table->index(['user_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
