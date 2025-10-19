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
        Schema::create('catalogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("item_name_" . FL, 100);
            $table->string("item_name_" . SL, 100);
            $table->longText("description_" . FL)->nullable();
            $table->longText("description_" . SL)->nullable();
            
            $table->foreignId('category_id')->nullable();//->constrained('categories');
            $table->foreignId('brand_id')->nullable();//->constrained('brands');
            $table->foreignId('origin_country_id')->nullable();//->constrained('countries');
            $table->enum('catalog_type',['single', 'variants']);
            $table->string("catalog_sku");
            $table->string("tags");
            $table->string('seo_desc')->nullable();
            $table->string('seo_keywords');
            $table->string("item_slug", 150);
            $table->string("video_providor", 20)->nullable();
            $table->string("link_providor", 55)->nullable();

            $table->boolean("seller_published")->default(0);
            $table->boolean("store_published")->default(0);
            $table->boolean("refundable")->default(0);
            $table->boolean("cod_allowed")->default(0);
            $table->boolean("taxable")->default(0);
            $table->boolean("featured")->default(0);
            
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->foreignUuid('updated_by')->nullable()->constrained('users');
            $table->boolean("approved")->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
