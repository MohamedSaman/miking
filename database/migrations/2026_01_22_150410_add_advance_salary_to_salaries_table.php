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
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('advance_salary', 10, 2)->default(0)->after('bonus')->comment('Advance payment given to staff');
            $table->string('advance_status')->default('pending')->after('advance_salary')->comment('pending, paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['advance_salary', 'advance_status']);
        });
    }
};
