<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderConfirmationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load(['customer']);

        if (! $order->customer) {
            return;
        }

        $order->customer->notify(new OrderPlacedNotification($order));
    }
}
