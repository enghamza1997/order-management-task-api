<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'sub_total' => $this->sub_total,
            'total_amount' => $this->total_amount,
            'order_status' => $this->order_status,
            'payment_method' => $this->payment_method,
            'packages' => OrderPackageResource::collection($this->orderPackages),
        ];
    }
}
