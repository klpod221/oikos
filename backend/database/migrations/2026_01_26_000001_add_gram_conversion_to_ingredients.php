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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('gram_conversion_factor', 10, 2)->nullable()->after('unit');
            // NULL = ingredient already measured in grams
            // Non-NULL = conversion factor (e.g., 1 piece = 50g)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('gram_conversion_factor');
        });
    }
};
