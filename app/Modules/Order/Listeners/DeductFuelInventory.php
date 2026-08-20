<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Fuel\Services\FuelService;
use App\Modules\Order\Events\OrderDispatched;
use App\Modules\Order\Notifications\OrderOutForDeliveryNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeductFuelInventory implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public int $tries = 3;

    public function __construct(private readonly FuelService $fuelService) {}

    public function handle(OrderDispatched $event): void
    {
        $order = $event->order->load(['customer', 'items']);

        // Notify customer that fuel is on the way
        if ($order->customer) {
            $order->customer->notify(new OrderOutForDeliveryNotification($order));
        }

        // Deduct inventory for each item in the order
        foreach ($order->items as $item) {
            try {
                $this->fuelService->deductForOrder(
                    orderId: $order->id,
                    vendorId: $item->vendor_id ?? $order->vendor_id,
                    productId: $item->product_id,
                    quantity: (float) $item->quantity
                );
            } catch (Throwable $e) {
                Log::error('[DeductFuelInventory] Failed to deduct fuel inventory for item.', [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }
}
