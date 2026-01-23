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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['retail_cash_bonus', 'retail_credit_bonus', 'wholesale_cash_bonus', 'wholesale_credit_bonus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('retail_cash_bonus', 10, 2)->default(0)->nullable();
            $table->decimal('retail_credit_bonus', 10, 2)->default(0)->nullable();
            $table->decimal('wholesale_cash_bonus', 10, 2)->default(0)->nullable();
            $table->decimal('wholesale_credit_bonus', 10, 2)->default(0)->nullable();
        });
    }
};
