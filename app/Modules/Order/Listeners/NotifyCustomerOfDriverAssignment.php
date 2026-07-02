<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderAssigned;
use App\Modules\Order\Notifications\DriverAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCustomerOfDriverAssignment implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(OrderAssigned $event): void
    {
        $order = $event->order->load(['customer', 'driver']);

        if (! $order->customer) {
            return;
        }

        $order->customer->notify(new DriverAssignedNotification($order));
    }
}
