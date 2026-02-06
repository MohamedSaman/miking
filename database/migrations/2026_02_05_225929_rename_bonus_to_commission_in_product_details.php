<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Simplify commission system: Remove 4 bonus fields, add 2 commission fields
     */
    public function up(): void
    {
        // Drop old bonus columns if they exist
        $columnsToDrop = ['retail_cash_bonus', 'retail_credit_bonus', 'wholesale_cash_bonus', 'wholesale_credit_bonus', 'sale_bonus', 'cash_bonus', 'credit_bonus'];
        
        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('product_details', $column)) {
                Schema::table('product_details', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        // Add new simple commission fields if they don't exist
        Schema::table('product_details', function (Blueprint $table) {
            if (!Schema::hasColumn('product_details', 'cash_sale_commission')) {
                $table->decimal('cash_sale_commission', 10, 2)->default(0.00)->after('unit');
            }
        });

        Schema::table('product_details', function (Blueprint $table) {
            if (!Schema::hasColumn('product_details', 'credit_sale_commission')) {
                $table->decimal('credit_sale_commission', 10, 2)->default(0.00)->after('cash_sale_commission');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            // Remove new commission columns
            if (Schema::hasColumn('product_details', 'cash_sale_commission')) {
                $table->dropColumn('cash_sale_commission');
            }
            if (Schema::hasColumn('product_details', 'credit_sale_commission')) {
                $table->dropColumn('credit_sale_commission');
            }
            
            // Restore old bonus columns
            $table->decimal('retail_cash_bonus', 10, 2)->default(0.00)->after('unit');
            $table->decimal('retail_credit_bonus', 10, 2)->default(0.00)->after('retail_cash_bonus');
            $table->decimal('wholesale_cash_bonus', 10, 2)->default(0.00)->after('retail_credit_bonus');
            $table->decimal('wholesale_credit_bonus', 10, 2)->default(0.00)->after('wholesale_cash_bonus');
        });
    }
};
