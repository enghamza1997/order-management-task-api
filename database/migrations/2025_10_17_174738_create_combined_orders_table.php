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
        Schema::create('combined_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_code', 100)->unique();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('address');

            // Monetary fields should use decimal
            $table->decimal('sub_total', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipment_fees', 15, 2)->default(0);
            $table->decimal('payment_fees', 15, 2)->default(0);
            $table->decimal('coupon_discount', 15, 2)->default(0);
            $table->decimal('seller_discount', 15, 2)->default(0);
            $table->decimal('commission_amount', 5, 2)->default(0); // as percentage
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
        Schema::dropIfExists('combined_orders');
    }
};
