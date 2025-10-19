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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_code', 100)->unique();

            // clearer and correct foreign key naming
            $table->foreignUuid('combined_order_id')
                ->constrained('combined_orders')
                ->cascadeOnDelete();

            $table->foreignUuid('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Use decimal for monetary values
            $table->decimal('sub_total', 15, 2);
            $table->decimal('tax', 15, 2);
            $table->decimal('shipment_fees', 15, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->decimal('seller_discount', 15, 2);
            $table->decimal('total_amount', 15, 2);

            $table->enum('order_status', ORDER_STATUS)->default(ORDER_STATUS[0]);
            $table->enum('payment_status', PAYMENT_STATUS)->default(PAYMENT_STATUS[1]);
            $table->string('payment_method', 100)->nullable();

            $table->integer('items_count')->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
