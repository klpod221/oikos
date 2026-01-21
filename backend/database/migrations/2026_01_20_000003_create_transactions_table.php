<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Transactions table - Stores all financial transactions
     * Links to user, wallet, and category
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict'); // Prevent category deletion if transactions exist

            $table->enum('type', ['income', 'expense']); // Transaction type
            $table->decimal('amount', 15, 2); // Transaction amount (always positive)
            $table->text('description')->nullable(); // Transaction note/description
            $table->date('transaction_date'); // Date of the transaction

            // Optional: for recurring transactions or transfers
            $table->string('reference')->nullable(); // External reference number
            $table->json('metadata')->nullable(); // Additional data (e.g., location, receipt image URL)

            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'transaction_date']); // For date range queries
            $table->index(['wallet_id', 'transaction_date']); // For wallet transaction history
            $table->index(['user_id', 'type', 'transaction_date']); // For income/expense reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
