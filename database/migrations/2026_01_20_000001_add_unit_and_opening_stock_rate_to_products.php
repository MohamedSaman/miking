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
        // Add 'unit' field to product_details table
        Schema::table('product_details', function (Blueprint $table) {
            $table->enum('unit', ['Bundle', 'Dozen', 'Piece'])->default('Piece')->after('status');
        });

        // Add 'opening_stock_rate' field to product_stocks table
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->decimal('opening_stock_rate', 10, 2)->default(0.00)->after('available_stock')->comment('Rate at which opening stock was purchased');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropColumn('opening_stock_rate');
        });
    }
};
