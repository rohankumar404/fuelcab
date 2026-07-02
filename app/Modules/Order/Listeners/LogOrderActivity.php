<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogOrderActivity implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        Log::channel('orders')->info('New order created', [
            'order_id'    => $order->id,
            'customer_id' => $order->customer_id,
            'vendor_id'   => $order->vendor_id,
            'total'       => $order->total_amount,
            'status'      => $order->status?->value,
            'created_at'  => $order->created_at,
        ]);
    }
}
