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
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'sku'               => $this->sku,
            'description'       => $this->description,
            'short_description' => $this->short_description,
            'full_description'  => $this->full_description,
            'product_image'     => $this->product_image,
            'icon'              => $this->icon,
            'price_per_unit'    => (float) $this->price_per_unit,
            'unit_of_measure'   => $this->unit_of_measure instanceof \BackedEnum ? $this->unit_of_measure->value : $this->unit_of_measure,
            'status'            => $this->status,
            'is_active'         => (bool) $this->is_active,
            'ordering_enabled'  => (bool) $this->ordering_enabled,
            'is_coming_soon'    => (bool) $this->is_coming_soon,
            'is_featured'       => (bool) $this->is_featured,
            'min_order_quantity'=> $this->min_order_quantity ? (float) $this->min_order_quantity : null,
            'max_order_quantity'=> $this->max_order_quantity ? (float) $this->max_order_quantity : null,
            'seo_title'         => $this->seo_title,
            'seo_description'   => $this->seo_description,
            'display_order'     => (int) $this->display_order,
            'category'          => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'vendor'           => $this->whenLoaded('vendor', fn () => [
                'id'            => $this->vendor->id,
                'brand_name'    => $this->vendor->brand_name,
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
