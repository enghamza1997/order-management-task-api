<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class PackageItem extends Model
{
    use HasFactory, HasUuids;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'package_id',
        'item_id',
        'warehouse_id',
        'commission_amount',
        'quantity',
        'price',
        'item_status'
    ];
    
    public function item()
    {
        return $this->belongsTo(ListedItem::class, 'item_id');
    }
    public function orderPackage()
    {
        return $this->belongsTo(OrderPackage::class, 'package_id');
    }

    public function getItemSubTotal()
    {
        return $this->price * $this->quantity;
    }
}
