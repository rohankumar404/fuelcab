<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\DeliveryCompletedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendDeliveryCompletedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order->load(['customer', 'driver', 'items.product']);

        if (! $order->customer?->email) {
            return;
        }

        $item        = $order->items->first();
        $productName = $item?->product?->name ?? 'Fuel Product';
        $quantity    = (float) ($item?->quantity ?? 0);
        $driverName  = $order->driver?->name ?? 'FuelCab Delivery';
        $plate       = $order->vehicle_registration_number ?? 'N/A';
        $completedAt = $order->delivered_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A');

        try {
            SendEmailJob::dispatch(
                $order->customer->email,
                new DeliveryCompletedMail(
                    customerName: $order->customer->name,
                    orderNumber:  $order->id,
                    productName:  $productName,
                    quantity:     $quantity,
                    driverName:   $driverName,
                    licensePlate: $plate,
                    completedAt:  $completedAt,
                    orderId:      $order->id
                )
            );
        } catch (\Throwable $e) {
            Log::error('[SendDeliveryCompletedNotification] Failed to queue delivery completed email', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
