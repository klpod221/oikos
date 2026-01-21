<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Savings Goals table - Stores user's savings targets
     * Allows users to set financial goals and track progress
     */
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name'); // Goal name (e.g., "New Laptop", "Vacation")
            $table->text('description')->nullable();

            $table->decimal('target_amount', 15, 2); // Target amount to save
            $table->decimal('current_amount', 15, 2)->default(0); // Current saved amount
            $table->string('currency', 3)->default('VND'); // ISO 4217 currency code

            $table->date('start_date')->nullable(); // When saving started
            $table->date('deadline')->nullable(); // Target completion date

            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');

            $table->string('icon', 50)->nullable(); // Icon identifier
            $table->string('color', 7)->nullable(); // Hex color code

            $table->timestamps();

            // Index for user's goals listing
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
