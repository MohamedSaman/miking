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
        Schema::create('staff_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_details')->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('sale_type', ['wholesale', 'retail']);
            $table->enum('payment_method', ['cash', 'credit']);
            $table->decimal('bonus_per_unit', 10, 2)->default(0);
            $table->decimal('total_bonus', 10, 2)->default(0);
            $table->timestamps();

            // Indexes for better query performance
            $table->index('staff_id');
            $table->index('sale_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_bonuses');
    }
};
