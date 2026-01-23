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
            // Add new dispatch_location column
            $table->string('dispatch_location')->after('staff_name');
            
            // Drop old columns
            $table->dropColumn(['start_location', 'end_location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_distributions', function (Blueprint $table) {
            // Restore old columns
            $table->string('start_location')->after('staff_name');
            $table->string('end_location')->after('start_location');
            
            // Drop new column
            $table->dropColumn('dispatch_location');
        });
    }
};
