<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderDispatched;
use App\Modules\Order\Notifications\OrderOutForDeliveryNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeductFuelInventory implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderDispatched $event): void
    {
        $order = $event->order->load(['customer']);

        // Notify customer that fuel is on the way
        if ($order->customer) {
            $order->customer->notify(new OrderOutForDeliveryNotification($order));
        }

        // TODO: Deduct fuel inventory via the Fuel/Inventory module on dispatch.
        // This ensures physical stock is reduced when fuel leaves the depot.
        // InventoryService::deductForOrder($order);
    }
}
