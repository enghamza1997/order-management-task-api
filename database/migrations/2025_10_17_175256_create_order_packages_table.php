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
        Schema::create('order_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->text('pickup_address');

            // Use decimal for monetary values
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipment_fees', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('cod', 15, 2)->nullable();

            $table->string('tracking_number')->nullable();
            $table->text('package_details')->nullable();
            $table->string('shipment_message')->nullable();

            $table->enum('package_status', ['pending', 'confirmed', 'delivered', 'canceled', 'returned'])->default('pending');
            $table->string('shipment_status')->nullable();

            $table->date('delivery_date')->nullable();

            $table->boolean('fullfilled')->default(false);
            $table->integer('items_count')->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_packages');
    }
};
