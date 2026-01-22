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
        Schema::table('product_details', function (Blueprint $table) {
            // Rename existing fields to Retail
            $table->renameColumn('cash_sale_bonus', 'retail_cash_bonus');
            $table->renameColumn('credit_sale_bonus', 'retail_credit_bonus');
        });

        Schema::table('product_details', function (Blueprint $table) {
            // Add Wholesale fields
            $table->decimal('wholesale_cash_bonus', 10, 2)->default(0.00)->after('retail_credit_bonus');
            $table->decimal('wholesale_credit_bonus', 10, 2)->default(0.00)->after('wholesale_cash_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn(['wholesale_cash_bonus', 'wholesale_credit_bonus']);
            
            $table->renameColumn('retail_cash_bonus', 'cash_sale_bonus');
            $table->renameColumn('retail_credit_bonus', 'credit_sale_bonus');
        });
    }
};
