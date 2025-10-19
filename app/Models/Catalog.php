<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Catalog extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'item_name_' . SL,
        'item_name_' . FL,
        'description_' . SL,
        'description_' . FL,
        'catalog_sku',
        'category_id',
        'origin_country_id',
        'brand_id',
        'tags',
        'seo_desc',
        'seo_keywords',
        'item_slug',
        'video_providor',
        'link_providor',
        'catalog_type',
        'cod_allowed',
        'featured',
        'refundable',
        'taxable',
        'approved',
        'seller_published',
        'store_published',
        'approved_at',
        'approved_by',
        'updated_by',
        'created_by'
    ];


    /**
     * Get all of the items for the Catalog
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CatalogItem::class, 'catalog_id');
    }
}
