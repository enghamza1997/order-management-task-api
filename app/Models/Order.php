<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Order extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'combined_order_id',
        'seller_id',
        'order_code',
        'sub_total',
        'shipment_fees',
        'commission_amount',
        'seller_discount',
        'tax',
        'total_amount',
        'order_status',
        'payment_method',
        'payment_status',
        'items_count',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    public function combinedOrder()
    {
        return $this->belongsTo(CombinedOrder::class, 'combined_order_id');
    }
    public function orderPackages()
    {
        return $this->hasMany(OrderPackage::class, 'order_id');
    }
}
