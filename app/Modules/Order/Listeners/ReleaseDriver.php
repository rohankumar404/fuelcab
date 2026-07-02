<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReleaseDriver implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        if (! $order->driver_id) {
            return; // No driver was assigned, nothing to release
        }

        // TODO: Update driver availability/status via Driver module.
        // DriverAvailabilityService::release($order->driver_id);
        Log::info('OrderModule: Driver released from cancelled order', [
            'order_id'  => $order->id,
            'driver_id' => $order->driver_id,
        ]);
    }
}
