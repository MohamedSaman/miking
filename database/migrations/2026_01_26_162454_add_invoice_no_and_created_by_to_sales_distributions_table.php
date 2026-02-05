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
        Schema::table('sales_distributions', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('handover_to');
            $table->string('selection_type')->default('products')->after('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_distributions', function (Blueprint $table) {
            $table->dropColumn(['invoice_no', 'selection_type']);
        });
    }
};
