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
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('sale_price_type', ['cash', 'credit', 'cash_credit'])
                ->default('cash')
                ->after('sale_type')
                ->comment('Price type used for calculation: cash, credit, or cash_credit (partial)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('sale_price_type');
        });
    }
};
