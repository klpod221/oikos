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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();

            // Preferences
            $table->string('currency')->default('VND');
            $table->string('gold_unit')->default('lượng'); // oz, lượng, chỉ
            $table->string('silver_unit')->default('lượng');
            $table->string('language')->default('vi');
            $table->string('theme')->default('system'); // light, dark, system

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
