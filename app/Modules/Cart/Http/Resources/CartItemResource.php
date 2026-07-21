<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $salesChannel = is_object($this->sales_channel) ? $this->sales_channel->value : ($this->sales_channel ?? 'direct');

        return [
            'id'                     => $this->id,
            'sales_channel'          => $salesChannel,
            'vendor_id'              => $this->vendor_id,
            'seller_name'            => $this->getSellerName(),
            'product_id'             => $this->product_id,
            'vendor_listing_id'      => $this->vendor_listing_id,
            'product_name_snapshot'  => $this->product_name_snapshot ?? $this->vendorListing?->listing_title ?? $this->product?->name,
            'product_sku_snapshot'   => $this->product_sku_snapshot ?? $this->vendorListing?->sku ?? $this->product?->sku,
            'quantity'               => (float) $this->quantity,
            'price_snapshot'         => (float) $this->price_snapshot,
            'unit_of_measure'        => $this->unit_snapshot ?? $this->unit_of_measure ?? 'units',
            'line_total'             => $this->getLineTotal(),
            'is_price_stale'         => $this->isPriceStale(),
            'product'                => $this->whenLoaded('product', fn () => [
                'id'              => $this->product->id,
                'name'            => $this->product->name,
                'slug'            => $this->product->slug,
                'current_price'   => (float) $this->product->price_per_unit,
                'unit_of_measure' => $this->product->unit_of_measure,
            ]),
            'vendor_listing'         => $this->whenLoaded('vendorListing', fn () => [
                'id'                 => $this->vendorListing->id,
                'title'              => $this->vendorListing->listing_title,
                'slug'               => $this->vendorListing->slug,
                'base_price'         => (float) $this->vendorListing->base_price,
                'unit'               => $this->vendorListing->unit,
                'available_quantity' => (float) $this->vendorListing->available_quantity,
                'min_order_quantity' => (float) $this->vendorListing->min_order_quantity,
            ]),
            'created_at'             => $this->created_at,
        ];
    }
}
