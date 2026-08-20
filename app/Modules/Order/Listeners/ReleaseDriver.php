<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Driver\Models\Driver;
use App\Modules\Order\Events\OrderCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReleaseDriver implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public int $tries = 3;

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        if (! $order->driver_id) {
            return; // No driver was assigned, nothing to release
        }

        // Retrieve driver by user_id
        $driver = Driver::where('user_id', $order->driver_id)->first();

        if ($driver && $driver->status === 'on_trip') {
            $driver->update(['status' => 'available']);

            Log::info('[ReleaseDriver] Driver released from cancelled order.', [
                'order_id' => $order->id,
                'driver_id' => $order->driver_id,
                'status' => 'available',
            ]);
        }
    }
}
