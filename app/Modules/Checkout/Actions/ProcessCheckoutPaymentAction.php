<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Enums\SalesChannel;
use App\Modules\Checkout\DTOs\CheckoutResultDTO;
use App\Modules\Checkout\DTOs\FulfillmentGroupDTO;
use App\Modules\Checkout\Models\Checkout;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ProcessCheckoutPaymentAction
 *
 * ORDER GROUPING LOGIC
 * ─────────────────────────────────────────────────────────────────────────
 * A single customer cart may contain items from multiple fulfillment contexts
 * (FuelCab Direct + one or more Marketplace vendors).
 *
 * On payment this action:
 *   1. Groups cart items by (sales_channel + vendor_id) using Cart::groupByFulfillment()
 *   2. Creates ONE Order per fulfillment group
 *   3. Writes immutable snapshots into every OrderItem:
 *        - sales_channel         → 'direct' | 'marketplace'
 *        - vendor_id             → UUID of the fulfilling vendor
 *        - product_name_snapshot → product name at time of order
 *        - product_sku_snapshot  → SKU at time of order
 *        - unit_snapshot         → unit at time of order
 *        - price_per_unit        → price locked from cart item snapshot
 *   4. Creates ONE parent Payment record covering the full checkout total
 *   5. Clears the cart
 *   6. Returns a CheckoutResultDTO with all orders + the payment
 *
 * Example:
 *   Cart: 500L Direct Diesel + 2MT Vendor-A Biomass + 1MT Vendor-B RDF
 *   → Order #1  channel=direct,       vendor=FuelCab Direct   (1 item)
 *   → Order #2  channel=marketplace,  vendor=Vendor A         (1 item)
 *   → Order #3  channel=marketplace,  vendor=Vendor B         (1 item)
 *   → Payment #1 checkout_id=X, amount=total_of_all_three_orders
 */
class ProcessCheckoutPaymentAction
{
    public function __construct(
        private readonly GroupCartItemsAction $groupCartItems,
    ) {}

    public function execute(string $userId, string $checkoutId, string $paymentMethod): CheckoutResultDTO
    {
        return DB::transaction(function () use ($userId, $checkoutId, $paymentMethod) {

            // ── 1. Load checkout with all relations ───────────────────────
            $checkout = Checkout::with(['cart.items.product', 'address'])
                ->where('user_id', $userId)
                ->where('status', 'draft')
                ->findOrFail($checkoutId);

            // ── 2. Pre-flight guards ──────────────────────────────────────
            if (! $checkout->address_id) {
                throw new \DomainException('Delivery address must be selected before payment.');
            }
            if (! $checkout->scheduled_delivery_at) {
                throw new \DomainException('Delivery slot must be scheduled before payment.');
            }
            if ($checkout->cart->isEmpty()) {
                throw new \DomainException('Cannot complete checkout with an empty cart.');
            }

            // ── 3. Group cart items into fulfillment groups ───────────────
            /** @var Collection<int, FulfillmentGroupDTO> $groups */
            $groups = $this->groupCartItems->execute($checkout->cart);

            if ($groups->isEmpty()) {
                throw new \DomainException('No valid fulfillment groups found in cart.');
            }

            // ── 4. Create one Order per fulfillment group ─────────────────
            $orders = new Collection();

            foreach ($groups as $group) {
                $groupSubtotal  = $group->subtotal;
                $gstRate        = 0.18;
                $deliveryFee    = (float) ($checkout->delivery_fee / $groups->count()); // split delivery fee equally
                $taxAmount      = round(($groupSubtotal + $deliveryFee) * $gstRate, 2);
                $totalAmount    = round($groupSubtotal + $deliveryFee + $taxAmount, 2);

                $order = Order::create([
                    'customer_id'           => $checkout->user_id,
                    'vendor_id'             => $group->vendorId ?? $checkout->vendor_id,
                    'delivery_address_id'   => $checkout->address_id,
                    'status'                => \App\Modules\Order\Enums\OrderStatus::Pending,
                    'channel'               => $group->salesChannel,
                    'subtotal_amount'       => $groupSubtotal,
                    'delivery_fee'          => round($deliveryFee, 2),
                    'tax_amount'            => $taxAmount,
                    'total_amount'          => $totalAmount,
                    'scheduled_delivery_at' => $checkout->scheduled_delivery_at,
                ]);

                // ── 5. Write immutable OrderItem snapshots ────────────────
                foreach ($group->items as $cartItem) {
                    $pricePerUnit = (float) $cartItem->price_snapshot;
                    $totalPrice   = round($cartItem->quantity * $pricePerUnit, 2);

                    // Resolve unit string (may be enum or plain string)
                    $unitSnapshot = $cartItem->unit_of_measure instanceof \BackedEnum
                        ? $cartItem->unit_of_measure->value
                        : ($cartItem->unit_of_measure ?? 'units');

                    // Resolve product snapshot fields from live product or cached snapshot
                    $product = $cartItem->product;

                    OrderItem::create([
                        'order_id'              => $order->id,
                        'product_id'            => $cartItem->product_id,
                        'quantity'              => $cartItem->quantity,
                        'price_per_unit'        => $pricePerUnit,
                        'total_price'           => $totalPrice,
                        // ─── Immutable historical snapshots ──────────────
                        'sales_channel'         => $group->salesChannel,
                        'vendor_id'             => $group->vendorId,
                        'product_name_snapshot' => $cartItem->product_name_snapshot
                            ?? $product?->name
                            ?? 'Unknown Product',
                        'product_sku_snapshot'  => $product?->sku ?? null,
                        'unit_snapshot'         => $unitSnapshot,
                    ]);
                }

                $orders->push($order);
            }

            // ── 6. Create ONE parent Payment covering the full checkout ────
            $payment = Payment::create([
                'order_id'               => $orders->first()->id, // anchor to first order
                'payment_gateway'        => $paymentMethod,
                'gateway_transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                'amount'                 => $checkout->total_amount,
                'status'                 => 'completed',
            ]);

            // ── 7. Clear cart and mark checkout complete ──────────────────
            $checkout->cart->items()->delete();
            $checkout->cart->update(['vendor_id' => null]);

            $checkout->update([
                'status'         => 'completed',
                'payment_method' => $paymentMethod,
                'payment_status' => 'success',
            ]);

            // ── 8. Fire OrderCreated event for each order ─────────────────
            foreach ($orders as $order) {
                event(new \App\Modules\Order\Events\OrderCreated($order));
            }

            return new CheckoutResultDTO(
                orders:  $orders,
                payment: $payment,
            );
        });
    }
}
