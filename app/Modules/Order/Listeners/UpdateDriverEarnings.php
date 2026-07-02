<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Notifications\OrderDeliveredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateDriverEarnings implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order->load(['customer', 'driver']);

        // Notify customer of successful delivery
        if ($order->customer) {
            $order->customer->notify(new OrderDeliveredNotification($order));
        }

        // TODO: Credit driver earnings to the Wallet module.
        Log::info('OrderModule: Order completed — driver earnings pending credit', [
            'order_id'  => $order->id,
            'driver_id' => $order->driver_id,
            'total'     => $order->total_amount,
        ]);
    }
}
