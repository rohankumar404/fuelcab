<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'product_id'      => $this->product_id,
            'product'         => $this->whenLoaded('product', fn () => [
                'id'              => $this->product->id,
                'name'            => $this->product->name,
                'slug'            => $this->product->slug,
                'status'          => $this->product->status,
                'current_price'   => (float) $this->product->price_per_unit,
                'unit_of_measure' => $this->product->unit_of_measure,
                'is_price_stale'  => $this->isPriceStale(),
            ]),
            'quantity'        => (float) $this->quantity,
            'price_snapshot'  => (float) $this->price_snapshot,
            'unit_of_measure' => $this->unit_of_measure,
            'line_total'      => $this->getLineTotal(),
            'created_at'      => $this->created_at,
        ];
    }
}
