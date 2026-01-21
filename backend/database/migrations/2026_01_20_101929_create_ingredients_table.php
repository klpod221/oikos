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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_global')->default(false);

            $table->string('name');
            $table->string('unit')->default('g'); // e.g., g, ml, pcs

            // Nutrition per 100g/100ml or per unit
            $table->decimal('calories', 8, 2)->default(0); // kcal
            $table->decimal('protein', 8, 2)->default(0); // g
            $table->decimal('carbs', 8, 2)->default(0); // g
            $table->decimal('fat', 8, 2)->default(0); // g
            $table->decimal('fiber', 8, 2)->default(0); // g
            $table->decimal('sugar', 8, 2)->default(0); // g

            $table->string('image', 255)->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['is_global']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
