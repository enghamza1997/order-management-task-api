<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderPackageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pickup_address' => $this->pickup_address,
            'sub_total' => $this->sub_total,
            'total_amount' => $this->total_amount,
            'shipment_status' => $this->shipment_status,
            'package_status' => $this->package_status,
            'items' => PackageItemResource::collection($this->packageItems),
        ];
    }
}
