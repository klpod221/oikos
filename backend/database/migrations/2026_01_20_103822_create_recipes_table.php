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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('prep_time')->nullable(); // minutes
            $table->integer('cooking_time')->nullable(); // minutes
            $table->integer('servings')->default(1);
            $table->string('image')->nullable();

            // Nutritional Cache (per serving)
            $table->decimal('calories', 10, 2)->nullable();
            $table->decimal('protein', 10, 2)->nullable();
            $table->decimal('carbs', 10, 2)->nullable();
            $table->decimal('fat', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
