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
            // Add customer_type_sale to distinguish wholesale vs retail
            $table->enum('customer_type_sale', ['wholesale', 'retail'])->default('retail')->after('customer_type');
            
            // Add payment_method to distinguish cash vs credit
            $table->enum('payment_method', ['cash', 'credit'])->default('cash')->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['customer_type_sale', 'payment_method']);
        });
    }
};
