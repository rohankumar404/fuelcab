<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TriggerPaymentSettlement implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        // TODO: Trigger vendor payment settlement via Payment module.
        Log::info('OrderModule: Payment settlement triggered for completed order', [
            'order_id'  => $order->id,
            'vendor_id' => $order->vendor_id,
            'total'     => $order->total_amount,
        ]);
    }
}
