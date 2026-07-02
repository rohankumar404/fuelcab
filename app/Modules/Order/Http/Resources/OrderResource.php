<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'customer_id'           => $this->customer_id,
            'customer'              => $this->whenLoaded('customer', fn () => [
                'id'    => $this->customer->id,
                'name'  => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'vendor_id'             => $this->vendor_id,
            'vendor'                => $this->whenLoaded('vendor', fn () => [
                'id'         => $this->vendor->id,
                'brand_name' => $this->vendor->brand_name,
            ]),
            'driver_id'             => $this->driver_id,
            'driver'                => $this->whenLoaded('driver', fn () => [
                'id'   => $this->driver->id,
                'name' => $this->driver->name,
            ]),
            'delivery_address_id'   => $this->delivery_address_id,
            'status'                => $this->status->value,
            'subtotal_amount'       => (float) $this->subtotal_amount,
            'delivery_fee'          => (float) $this->delivery_fee,
            'tax_amount'            => (float) $this->tax_amount,
            'total_amount'          => (float) $this->total_amount,
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->toIso8601String(),
            'delivered_at'          => $this->delivered_at?->toIso8601String(),
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),
        ];
    }
}
