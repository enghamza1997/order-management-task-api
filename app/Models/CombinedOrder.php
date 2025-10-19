<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class CombinedOrder extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'order_code',
        'address',
        'sub_total',
        'shipment_fees',
        'commission_amount',
        'payment_fees',
        'coupon_discount',
        'seller_discount',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'items_count',
    ];

    protected $casts = [
        'address' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'combined_order_id');
    }

    public function payments() { 
        return $this->hasMany(Payment::class);
    }
}
