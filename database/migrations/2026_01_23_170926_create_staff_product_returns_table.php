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
        Schema::create('staff_product_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_details')->onDelete('cascade');
            $table->integer('return_quantity')->comment('Quantity returned by staff');
            $table->integer('restock_quantity')->default(0)->comment('Quantity re-entered to stock by admin');
            $table->integer('damaged_quantity')->default(0)->comment('Quantity marked as damaged by admin');
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_product_returns');
    }
};
