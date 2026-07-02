<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateInvoice implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        // TODO: Generate PDF invoice and attach to the order.
        // InvoiceService::generateForOrder($order);
        Log::info('OrderModule: Invoice generation queued for order', [
            'order_id' => $order->id,
        ]);
    }
}
