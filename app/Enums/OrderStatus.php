<?php
namespace App\Enums;

enum OrderStatus:String {

    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELED = 'canceled';
    case DELIVERED = 'delivered';   
    case RETURNED = 'returned';
}