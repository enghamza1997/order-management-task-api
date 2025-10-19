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
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('catalog_id')->nullable()->constrained('catalogs');
            $table->string('item_title_' . FL, 255)->nullable();
            $table->string('item_title_' . SL, 255)->nullable();
            $table->string('item_sku', 255);
            $table->string('seo_keywords')->nullable();
            $table->string('identifier', 20)->nullable();
            $table->boolean('featured_product')->default(0);
            $table->boolean("seller_published")->default(0);
            $table->boolean("store_published")->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->foreignUuid('updated_by')->nullable()->constrained('users');
            $table->boolean("approved")->default(0);
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
