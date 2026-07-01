<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'vendor_id'  => $this->vendor_id,
            'vendor'     => $this->whenLoaded('vendor', fn () => [
                'id'         => $this->vendor->id,
                'brand_name' => $this->vendor->brand_name,
            ]),
            'items'      => CartItemResource::collection($this->whenLoaded('items')),
            'item_count' => $this->items->count(),
            'total'      => $this->getTotal(),
            'is_empty'   => $this->isEmpty(),
            'updated_at' => $this->updated_at,
        ];
    }
}
