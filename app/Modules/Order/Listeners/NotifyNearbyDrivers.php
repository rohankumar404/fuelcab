<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Finds and notifies available nearby drivers of a new order.
 * Full implementation requires a geospatial driver-matching service.
 * This fires an async job (placeholder) for driver radius search.
 */
class NotifyNearbyDrivers implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load(['deliveryAddress', 'vendor']);

        Log::info('OrderModule: Broadcasting new order to nearby drivers', [
            'order_id'  => $order->id,
            'vendor_id' => $order->vendor_id,
        ]);

        // TODO: Dispatch FindNearbyDriversJob when geospatial service is ready.
        // FindNearbyDriversJob::dispatch($order);
    }
}
