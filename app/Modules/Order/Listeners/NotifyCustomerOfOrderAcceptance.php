<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderAccepted;
use App\Modules\Order\Notifications\OrderAcceptedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCustomerOfOrderAcceptance implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(OrderAccepted $event): void
    {
        $order = $event->order->load(['customer']);

        if (! $order->customer) {
            return;
        }

        $order->customer->notify(new OrderAcceptedNotification($order));
    }
}
