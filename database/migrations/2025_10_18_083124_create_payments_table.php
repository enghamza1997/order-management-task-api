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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique(); // gateway-level id (simulated)
            $table->integer('combined_order_id')->constrained('combined_orders')->cascadeOnDelete();
            $table->enum('status',['pending','successful','failed'])->default('pending');
            $table->string('payment_method');
            $table->decimal('amount',12,2);
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
