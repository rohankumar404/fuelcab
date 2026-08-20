<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\OrderCancelledMail;
use App\Modules\Order\Events\OrderCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderCancellationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order->load(['customer', 'items.product']);

        if (! $order->customer?->email) {
            return;
        }

        $item = $order->items->first();
        $productName = $item?->product?->name ?? 'Fuel Product';
        $quantity = (float) ($item?->quantity ?? 0);

        try {
            SendEmailJob::dispatch(
                $order->customer->email,
                new OrderCancelledMail(
                    customerName: $order->customer->name,
                    orderNumber: $order->id,
                    productName: $productName,
                    quantity: $quantity
                )
            );
        } catch (\Throwable $e) {
            Log::error('[SendOrderCancellationNotification] Failed to queue cancellation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
