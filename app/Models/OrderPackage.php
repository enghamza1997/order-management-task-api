<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class OrderPackage extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'pickup_address',
        'sub_total',
        'shipment_fees',
        'total_amount',
        'tracking_number',
        'package_details',
        'cod',
        'shipment_message',
        'shipment_status',
        'package_status',
        'delivery_date',
        'fullfilled',
        'items_count',
    ];

    protected $casts = [
        'pickup_address' => 'array',
        'package_details' => 'array',
        'fulfilled' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    // public function warehouse()
    // {
    //     return $this->belongsTo(Warehouse::class);
    // }
    public function packageItems()
    {
        return $this->hasMany(PackageItem::class, 'package_id');
    }
}
