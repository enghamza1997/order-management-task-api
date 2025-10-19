<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ListedItem extends Model
{
    use HasFactory, HasUuids;
    protected $fillable =
    [
        "user_id",
        "warehouse_id",
        "catalog_item_id",
        "price",
        "quantity",
        "internal_sku",
        "listed_sku",
        "note",
        "warranty_time",
        "agent_guarantee",
        "free_shipping",
        "published",
        "processing_time"
    ];

    public function seller()
    {
        return $this->belongsTo(User::class);
    }
    public function getPrice(string $spliter = 'EGP')
    {
        return $this->price .' '. $spliter;
    }

    public function catalogItem()
    {
        return $this->belongsTo(CatalogItem::class, 'catalog_item_id');
    }

    // public function warehouse()
    // {
    //     return $this->belongsTo(warehouse::class);
    // }

}
