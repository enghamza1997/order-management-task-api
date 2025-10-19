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
        Schema::create('package_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('package_id')->constrained('order_packages');
            $table->foreignUuid('item_id')->constrained('listed_items');
            $table->foreignId('warehouse_id');//->constrained('warehouses');
            $table->float('commission_amount');
            $table->integer('quantity');
            $table->float('price');
            $table->enum('item_status', ['pending', 'delivered', 'confirmed', 'canceled', 'returned']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_items');
    }
};
