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
        Schema::table('product_prices', function (Blueprint $table) {
            // Add cash_price, credit_price, cash_credit_price
            if (!Schema::hasColumn('product_prices', 'cash_price')) {
                $table->decimal('cash_price', 10, 2)->nullable()->after('selling_price')
                    ->comment('Price for cash transactions');
            }
            if (!Schema::hasColumn('product_prices', 'credit_price')) {
                $table->decimal('credit_price', 10, 2)->nullable()->after('cash_price')
                    ->comment('Price for credit transactions');
            }
            if (!Schema::hasColumn('product_prices', 'cash_credit_price')) {
                $table->decimal('cash_credit_price', 10, 2)->nullable()->after('credit_price')
                    ->comment('Price for partial/credit buyers (between cash and full credit)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            // Restore discount_price column
            $table->decimal('discount_price', 10, 2)->nullable()->after('selling_price');
            
            // Remove cash_credit_price column
            $table->dropColumn('cash_credit_price');
        });
    }
};
