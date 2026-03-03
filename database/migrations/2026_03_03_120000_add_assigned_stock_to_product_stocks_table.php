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
        Schema::table('product_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('product_stocks', 'assigned_stock')) {
                $table->integer('assigned_stock')->default(0)->after('available_stock')->comment('Quantity assigned to staff/sales but not yet finalized');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            if (Schema::hasColumn('product_stocks', 'assigned_stock')) {
                $table->dropColumn('assigned_stock');
            }
        });
    }
};
