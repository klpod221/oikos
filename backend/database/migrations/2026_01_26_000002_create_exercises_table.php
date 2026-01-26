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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_global')->default(false); // Admin-created global exercises

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();

            $table->enum('type', ['reps', 'time']); // Reps-based or Time-based
            $table->decimal('calories_per_unit', 8, 2)->nullable(); // calories per rep OR per minute

            $table->string('image')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['is_global', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
