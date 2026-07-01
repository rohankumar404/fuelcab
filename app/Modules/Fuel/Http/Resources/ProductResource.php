<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'sku'              => $this->sku,
            'description'      => $this->description,
            'price_per_unit'   => (float) $this->price_per_unit,
            'unit_of_measure'  => $this->unit_of_measure,
            'status'           => $this->status,
            'is_active'        => (bool) $this->is_active,
            'ordering_enabled' => $this->isOrderingEnabled(),
            'is_coming_soon'   => $this->isComingSoon(),
            'category'         => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'vendor'           => $this->whenLoaded('vendor', fn () => [
                'id'            => $this->vendor->id,
                'business_name' => $this->vendor->business_name,
            ]),
            'inventory'        => $this->whenLoaded('inventory', fn () => $this->inventory ? [
                'quantity_available'  => (float) $this->inventory->quantity_available,
                'low_stock_threshold' => (float) $this->inventory->low_stock_threshold,
                'last_restocked_at'   => $this->inventory->last_restocked_at,
                'is_low_stock'        => $this->inventory->quantity_available <= $this->inventory->low_stock_threshold,
            ] : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
