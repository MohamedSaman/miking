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
        Schema::table('staff_product_returns', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_product_returns', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processed'])->default('pending')->change();
        });
    }
};
