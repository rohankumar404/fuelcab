<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Resources;

use App\Modules\Order\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Determine if the authenticated customer can cancel this order
        $user = $request->user();
        $canCancel = $user?->hasRole('customer')
            && $this->customer_id === $user->id
            && $this->status?->canTransitionTo(OrderStatus::Cancelled);

        return [
            'id'                    => $this->id,
            'order_number'          => $this->order_number,
            'channel'               => $this->channel instanceof \BackedEnum
                ? $this->channel->value
                : $this->channel,
            'is_emergency'          => (bool) ($this->is_emergency ?? false),

            // ── Parties ──────────────────────────────────────────────────
            'customer_id'           => $this->customer_id,
            'customer'              => $this->whenLoaded('customer', fn () => [
                'id'    => $this->customer->id,
                'name'  => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone ?? null,
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
                'phone' => $this->driver->phone ?? null,
            ]),

            // ── Delivery Address ─────────────────────────────────────────
            'delivery_address_id'   => $this->delivery_address_id,
            'delivery_address'      => $this->whenLoaded('deliveryAddress', fn () => [
                'id'             => $this->deliveryAddress->id,
                'address_line_1' => $this->deliveryAddress->address_line_1,
                'address_line_2' => $this->deliveryAddress->address_line_2 ?? null,
                'city'           => $this->deliveryAddress->city,
                'state'          => $this->deliveryAddress->state,
                'postal_code'    => $this->deliveryAddress->postal_code,
                'full_address'   => $this->deliveryAddress->full_address ?? null,
            ]),

            // ── Status & Lifecycle ───────────────────────────────────────
            'status'                => $this->status?->value,
            'status_label'          => $this->status?->label(),
            'can_cancel'            => $canCancel,
            'cancel_reason'         => $this->cancel_reason ?? null,

            // ── Financials ───────────────────────────────────────────────
            'subtotal_amount'       => (float) $this->subtotal_amount,
            'delivery_fee'          => (float) $this->delivery_fee,
            'tax_amount'            => (float) $this->tax_amount,
            'emergency_fee'         => (float) ($this->emergency_fee ?? 0),
            'total_amount'          => (float) $this->total_amount,
            'payment_method'        => $this->payment_method ?? null,

            // ── Order Items ──────────────────────────────────────────────
            'items'                 => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'                    => $item->id,
                    'product_name_snapshot' => $item->product_name_snapshot,
                    'product_sku_snapshot'  => $item->product_sku_snapshot,
                    'unit_snapshot'         => $item->unit_snapshot,
                    'quantity'              => (float) $item->quantity,
                    'price_per_unit'        => (float) $item->price_per_unit,
                    'total_price'           => (float) $item->total_price,
                    'sales_channel'         => $item->sales_channel instanceof \BackedEnum
                        ? $item->sales_channel->value
                        : $item->sales_channel,
                ])
            ),

            // ── Status History ───────────────────────────────────────────
            'status_history'        => $this->whenLoaded('statusLogs', fn () =>
                $this->statusLogs->map(fn ($log) => [
                    'from_status' => $log->from_status,
                    'to_status'   => $log->to_status,
                    'reason'      => $log->reason ?? null,
                    'changed_at'  => $log->created_at?->toIso8601String(),
                ])
            ),

            // ── Timestamps ───────────────────────────────────────────────
            'notes'                 => $this->notes ?? null,
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->toIso8601String(),
            'delivered_at'          => $this->delivered_at?->toIso8601String(),
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),
        ];
    }
}
