<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'cart_id'               => $this->cart_id,
            'address_id'            => $this->address_id,
            'vendor_id'             => $this->vendor_id,
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->toDateTimeString(),
            'status'                => $this->status,
            'pricing_summary'       => [
                'subtotal_amount' => $this->subtotal_amount,
                'delivery_fee'    => $this->delivery_fee,
                'tax_amount'      => $this->tax_amount,
                'total_amount'    => $this->total_amount,
            ],
            'payment' => [
                'method' => $this->payment_method,
                'status' => $this->payment_status,
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
