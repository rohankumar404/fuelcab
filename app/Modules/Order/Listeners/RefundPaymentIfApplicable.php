<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Notifications\OrderCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RefundPaymentIfApplicable implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order->load(['customer']);

        // Notify customer of cancellation
        if ($order->customer) {
            $order->customer->notify(
                new OrderCancelledNotification($order, $event->reason)
            );
        }

        // TODO: Issue refund via Payment module when payment has been captured.
        Log::info('OrderModule: Order cancelled — refund evaluation pending', [
            'order_id'  => $order->id,
            'reason'    => $event->reason,
            'total'     => $order->total_amount,
        ]);
    }
}
