<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Enums\SalesChannel;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderPlaced;
use App\Modules\Order\Jobs\AssignDriverJob;
use App\Modules\Order\Jobs\SendOrderReceiptJob;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateOrderAction
{
    /**
     * Create a new order from a validated payload.
     *
     * @param  array{
     *   customer_id: string,
     *   vendor_id: string,
     *   delivery_address_id: string,
     *   items: array<array{product_id: string, vendor_id: string, quantity: float, price_per_unit: float, unit_of_measure: string}>,
     *   delivery_fee?: float,
     *   tax_amount?: float,
     *   channel?: string,
     *   payment_method?: string,
     *   notes?: string,
     *   scheduled_delivery_at?: string|null,
     * } $data
     */
    public function execute(array $data): Order
    {
        if (empty($data['items'])) {
            throw new InvalidArgumentException('An order must have at least one item.');
        }

        return DB::transaction(function () use ($data): Order {
            $items = $data['items'];
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0.0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0.0);

            // Calculate subtotal from line items
            $subtotal = collect($items)->sum(
                fn (array $item) => (float) $item['quantity'] * (float) $item['price_per_unit']
            );
            $total = round($subtotal + $deliveryFee + $taxAmount, 2);

            $commissionRate = (float) config('fuelcab.payment.commission_rate', 0.10);
            $commissionAmount = round($total * $commissionRate, 2);

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'vendor_id' => $data['vendor_id'],
                'delivery_address_id' => $data['delivery_address_id'],
                'status' => OrderStatus::Pending,
                'channel' => $data['channel'] ?? SalesChannel::Direct->value,
                'subtotal_amount' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'payment_method' => $data['payment_method'] ?? null,
                'notes' => $data['notes'] ?? null,
                'scheduled_delivery_at' => $data['scheduled_delivery_at'] ?? null,
                'order_number' => 'FC-'.strtoupper(Str::random(8)),
            ]);

            // Create order items
            foreach ($items as $item) {
                $lineTotal = round((float) $item['quantity'] * (float) $item['price_per_unit'], 2);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'vendor_id' => $item['vendor_id'] ?? $order->vendor_id,
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $item['price_per_unit'],
                    'unit_of_measure' => $item['unit_of_measure'] ?? 'liter',
                    'total_price' => $lineTotal,
                    'sales_channel' => $item['sales_channel'] ?? $order->channel,
                ]);
            }

            Log::info('[CreateOrderAction] Order created.', [
                'order_id' => $order->id,
                'order_num' => $order->order_number,
                'customer_id' => $order->customer_id,
                'total' => $total,
                'items_count' => count($items),
            ]);

            // Fire order placed event (notifies vendor, etc.)
            event(new OrderPlaced($order));

            // Auto-assign a driver asynchronously
            AssignDriverJob::dispatch($order->id)->delay(now()->addSeconds(10));

            // Send receipt email asynchronously
            SendOrderReceiptJob::dispatch($order->id);

            return $order->fresh(['items', 'customer', 'vendor', 'deliveryAddress']);
        });
    }
}
