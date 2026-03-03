<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL for MariaDB compatibility (CHANGE COLUMN instead of RENAME COLUMN)
        if (Schema::hasColumn('product_details', 'cash_sale_bonus')) {
            DB::statement('ALTER TABLE `product_details` CHANGE COLUMN `cash_sale_bonus` `retail_cash_bonus` DECIMAL(10,2) DEFAULT 0.00');
        }
        if (Schema::hasColumn('product_details', 'credit_sale_bonus')) {
            DB::statement('ALTER TABLE `product_details` CHANGE COLUMN `credit_sale_bonus` `retail_credit_bonus` DECIMAL(10,2) DEFAULT 0.00');
        }

        Schema::table('product_details', function (Blueprint $table) {
            // Add Wholesale fields if they don't exist
            if (!Schema::hasColumn('product_details', 'wholesale_cash_bonus')) {
                $table->decimal('wholesale_cash_bonus', 10, 2)->default(0.00)->after('retail_credit_bonus');
            }
            if (!Schema::hasColumn('product_details', 'wholesale_credit_bonus')) {
                $table->decimal('wholesale_credit_bonus', 10, 2)->default(0.00)->after('wholesale_cash_bonus');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            if (Schema::hasColumn('product_details', 'wholesale_cash_bonus')) {
                $table->dropColumn('wholesale_cash_bonus');
            }
            if (Schema::hasColumn('product_details', 'wholesale_credit_bonus')) {
                $table->dropColumn('wholesale_credit_bonus');
            }
        });

        // Use raw SQL for MariaDB compatibility
        if (Schema::hasColumn('product_details', 'retail_cash_bonus')) {
            DB::statement('ALTER TABLE `product_details` CHANGE COLUMN `retail_cash_bonus` `cash_sale_bonus` DECIMAL(10,2) DEFAULT 0.00');
        }
        if (Schema::hasColumn('product_details', 'retail_credit_bonus')) {
            DB::statement('ALTER TABLE `product_details` CHANGE COLUMN `retail_credit_bonus` `credit_sale_bonus` DECIMAL(10,2) DEFAULT 0.00');
        }
    }
};
