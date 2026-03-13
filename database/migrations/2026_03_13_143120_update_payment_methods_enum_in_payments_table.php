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
        // Add more methods to the enum to support internal adjustments
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'credit', 'bank_transfer', 'return_adjustment', 'credit_adjustment') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'credit', 'bank_transfer') DEFAULT 'cash'");
    }
};
