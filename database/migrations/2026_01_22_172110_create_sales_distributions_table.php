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
        Schema::create('sales_distributions', function (Blueprint $col) {
            $col->id();
            $col->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $col->string('staff_name');
            $col->string('start_location');
            $col->string('end_location');
            $col->decimal('distance_km', 10, 2);
            $col->decimal('travel_expense', 10, 2);
            $col->string('handover_to');
            $col->enum('status', ['pending', 'completed', 'approved'])->default('pending');
            $col->text('description')->nullable();
            $col->json('products')->nullable();
            $col->date('distribution_date');
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_distributions');
    }
};
