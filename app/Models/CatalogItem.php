<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogItem extends Model
{
   use HasFactory, HasUuids, SoftDeletes;

   protected $fillable = [
      'item_title_' . FL,
      'item_title_' . SL,
      'catalog_id',
      'sku',
      'tags',
      'identifier',
      'seo_keywords',
      'item_sku',
      'created_by',
      'updated_by',
      'featured_product',
      'seller_published',
      'store_published',
      'approved_by',
      'approved_at',
      'approved'
   ];

      /**
     * attach flash deals
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */

   /**
    * Get the Catalog that owns the CatalogItem
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
   public function catalog(): BelongsTo
   {
       return $this->belongsTo(Catalog::class, 'catalog_id');
   }

   public function listedItems()
   {
      return $this->hasMany(ListedItem::class, 'catalog_item_id');
   }
}
