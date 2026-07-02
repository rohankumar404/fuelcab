<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Modules\Checkout\Models\Checkout;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessCheckoutPaymentAction
{
    public function execute(string $userId, string $checkoutId, string $paymentMethod): Order
    {
        return DB::transaction(function () use ($userId, $checkoutId, $paymentMethod) {
            $checkout = Checkout::with(['cart.items.product', 'address', 'vendor'])
                ->where('user_id', $userId)
                ->where('status', 'draft')
                ->findOrFail($checkoutId);

            if (! $checkout->address_id) {
                throw new \DomainException("Delivery address must be selected before payment.");
            }

            if (! $checkout->vendor_id) {
                throw new \DomainException("Vendor must be selected before payment.");
            }

            if (! $checkout->scheduled_delivery_at) {
                throw new \DomainException("Delivery slot must be scheduled before payment.");
            }

            // 1. Create the Order
            $order = Order::create([
                'customer_id'           => $checkout->user_id,
                'vendor_id'             => $checkout->vendor_id,
                'delivery_address_id'   => $checkout->address_id,
                'status'                => \App\Modules\Order\Enums\OrderStatus::Pending,
                'subtotal_amount'       => $checkout->subtotal_amount,
                'delivery_fee'          => $checkout->delivery_fee,
                'tax_amount'            => $checkout->tax_amount,
                'total_amount'          => $checkout->total_amount,
                'scheduled_delivery_at' => $checkout->scheduled_delivery_at,
            ]);

            // 2. Create the Order Items
            foreach ($checkout->cart->items as $cartItem) {
                $pricePerUnit = $cartItem->price_snapshot ?? $cartItem->product?->price_per_unit ?? 0.00;
                $totalPrice = $cartItem->quantity * $pricePerUnit;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $cartItem->product_id,
                    'quantity'       => $cartItem->quantity,
                    'price_per_unit' => $pricePerUnit,
                    'total_price'    => round($totalPrice, 2),
                ]);
            }

            // 3. Create the Payment record
            Payment::create([
                'order_id'               => $order->id,
                'payment_gateway'        => $paymentMethod,
                'gateway_transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                'amount'                 => $checkout->total_amount,
                'status'                 => 'completed',
            ]);

            // 4. Clear Cart items
            $checkout->cart->items()->delete();
            $checkout->cart->update(['vendor_id' => null]);

            // 5. Update Checkout Session status
            $checkout->update([
                'status'         => 'completed',
                'payment_method' => $paymentMethod,
                'payment_status' => 'success',
            ]);

            // 6. Fire OrderCreated Event
            event(new \App\Modules\Order\Events\OrderCreated($order));

            return $order;
        });
    }
}
