<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderAssigned;
use App\Modules\Order\Notifications\NewOrderAssignedToDriverNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyDriverOfNewOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(OrderAssigned $event): void
    {
        $order = $event->order->load(['driver']);

        if (! $order->driver) {
            return;
        }

        $order->driver->notify(new NewOrderAssignedToDriverNotification($order));
    }
}
