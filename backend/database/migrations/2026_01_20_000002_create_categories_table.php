<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Categories table - Stores transaction categories
     * Supports both system-defined (admin) and user-defined categories
     * Also supports hierarchical structure with parent_id
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // NULL for system categories, user_id for custom categories
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('name');
            $table->enum('type', ['income', 'expense']); // Category type
            $table->enum('scope', ['system', 'custom'])->default('custom'); // system = global, custom = user-specific

            $table->string('icon', 50)->nullable(); // Icon identifier (e.g., "shopping-cart", "home")
            $table->string('color', 7)->nullable(); // Hex color code (e.g., "#FF5733")

            // Self-referencing for subcategories (e.g., Food -> Restaurant, Groceries)
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');

            $table->integer('sort_order')->default(0); // For custom ordering
            $table->boolean('is_active')->default(true); // Soft disable without deleting

            $table->timestamps();

            // Indexes for faster lookups
            $table->index(['scope', 'type']); // For listing system categories by type
            $table->index(['user_id', 'type']); // For listing user's categories by type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
