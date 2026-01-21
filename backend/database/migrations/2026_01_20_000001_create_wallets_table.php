<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Wallets table - Stores user's financial wallets
     * Each user can have multiple wallets (Cash, Bank, E-wallet, etc.)
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name'); // e.g., "Cash", "Vietcombank", "Momo"
            $table->decimal('balance', 15, 2)->default(0); // Current balance
            $table->string('currency', 3)->default('VND'); // ISO 4217 currency code
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false); // Default wallet for transactions
            $table->string('icon', 50)->nullable(); // Icon identifier
            $table->string('color', 7)->nullable(); // Hex color code

            $table->timestamps();

            // Index for faster user wallet lookups
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
