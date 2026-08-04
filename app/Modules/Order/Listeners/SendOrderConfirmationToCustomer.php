<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Notifications\OrderPlacedNotification;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load(['customer', 'items.product', 'deliveryAddress']);

        if (! $order->customer) {
            return;
        }

        // Send in-app database notification (existing behaviour)
        $order->customer->notify(new OrderPlacedNotification($order));

        // Send branded transactional email via queue
        if ($order->customer->email) {
            $item        = $order->items->first();
            $productName = $item?->product?->name ?? 'Fuel Product';
            $quantity    = $item?->quantity ?? 0;
            $address     = $order->deliveryAddress?->full_address ?? 'N/A';

            try {
                SendEmailJob::dispatch(
                    $order->customer->email,
                    new OrderConfirmationMail(
                        customerName:    $order->customer->name,
                        orderNumber:     $order->id,
                        productName:     $productName,
                        quantity:        $quantity,
                        status:          $order->status->label(),
                        total:           $order->total_amount,
                        deliveryAddress: $address,
                        orderId:         $order->id
                    )
                );
            } catch (\Throwable $e) {
                Log::error('[SendOrderConfirmationToCustomer] Failed to queue confirmation email', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}
