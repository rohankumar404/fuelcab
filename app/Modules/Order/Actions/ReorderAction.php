<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Enums\SalesChannel;
use App\Models\User;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReorderAction
{
    /**
     * Duplicate a past order into a new pending order with the same items.
     *
     * @throws \DomainException if the original order has no items or belongs to another customer
     */
    public function execute(string $originalOrderId, User $customer): Order
    {
        return DB::transaction(function () use ($originalOrderId, $customer) {
            $original = Order::with('items')
                ->where('customer_id', $customer->id)
                ->findOrFail($originalOrderId);

            if ($original->items->isEmpty()) {
                throw new \DomainException('Cannot reorder: original order contains no items.');
            }

            // Create new order with same financials and delivery address
            $newOrder = Order::create([
                'customer_id'         => $customer->id,
                'vendor_id'           => $original->vendor_id,
                'delivery_address_id' => $original->delivery_address_id,
                'status'              => OrderStatus::Pending,
                'channel'             => $original->channel,
                'subtotal_amount'     => $original->subtotal_amount,
                'delivery_fee'        => $original->delivery_fee,
                'tax_amount'          => $original->tax_amount,
                'total_amount'        => $original->total_amount,
                'is_emergency'        => false,
                'order_number'        => 'ORD-' . strtoupper(Str::random(8)),
                'notes'               => "Reorder from #{$original->order_number}",
            ]);

            // Duplicate all order items
            foreach ($original->items as $item) {
                OrderItem::create([
                    'order_id'              => $newOrder->id,
                    'product_id'            => $item->product_id,
                    'vendor_listing_id'     => $item->vendor_listing_id,
                    'quantity'              => $item->quantity,
                    'price_per_unit'        => $item->price_per_unit,
                    'total_price'           => $item->total_price,
                    'sales_channel'         => $item->sales_channel,
                    'vendor_id'             => $item->vendor_id,
                    'product_name_snapshot' => $item->product_name_snapshot,
                    'product_sku_snapshot'  => $item->product_sku_snapshot,
                    'unit_snapshot'         => $item->unit_snapshot,
                ]);
            }

            event(new OrderCreated($newOrder));

            return $newOrder->load('items');
        });
    }
}
