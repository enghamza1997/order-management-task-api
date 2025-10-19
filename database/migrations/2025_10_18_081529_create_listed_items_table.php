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
        Schema::create('listed_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->foreignUuid('catalog_item_id');//->constrained('catalog_items');
            $table->foreignid('warehouse_id')->nullable();//->constrained('warehouses');
            $table->double('price',6,2);
            $table->integer('quantity');
            $table->string('internal_sku',255)->nullable();
            $table->string('listed_sku',255);
            $table->string('note')->nullable();
            $table->string('warranty_time')->nullable();
            $table->tinyInteger('agent_guarantee')->default(0);
            $table->tinyInteger('free_shipping')->default(0);
            $table->tinyInteger('published')->default(0);;
            $table->string("processing_time")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listed_items');
    }
};
