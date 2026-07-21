<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sellerGroups = array_map(function ($group) {
            return [
                'sales_channel'  => $group['sales_channel'],
                'vendor_id'      => $group['vendor_id'],
                'seller_name'    => $group['seller_name'],
                'is_first_party' => $group['is_first_party'],
                'subtotal'       => $group['subtotal'],
                'items'          => CartItemResource::collection($group['items']),
            ];
        }, $this->groupByFulfillment());

        return [
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'guest_token'          => $this->guest_token,
            'items'                => CartItemResource::collection($this->whenLoaded('items')),
            'seller_groups'        => $sellerGroups,
            'item_count'           => $this->getItemCount(),
            'total'                => $this->getTotal(),
            'has_multiple_sellers' => $this->hasMultipleVendors(),
            'is_empty'             => $this->isEmpty(),
            'updated_at'           => $this->updated_at,
        ];
    }
}
