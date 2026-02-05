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
            // Remove old sale_bonus field
            $table->dropColumn('sale_bonus');
            
            // Add new cash and credit sale bonus fields
            $table->decimal('cash_sale_bonus', 10, 2)->default(0.00)->after('unit')->comment('Bonus for cash sales');
            $table->decimal('credit_sale_bonus', 10, 2)->default(0.00)->after('cash_sale_bonus')->comment('Bonus for credit sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn(['cash_sale_bonus', 'credit_sale_bonus']);
            
            // Restore old sale_bonus field
            $table->decimal('sale_bonus', 10, 2)->default(0.00)->after('unit');
        });
    }
};
