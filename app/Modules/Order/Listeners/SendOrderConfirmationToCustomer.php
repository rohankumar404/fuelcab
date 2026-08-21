<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\OrderConfirmationMail;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load(['customer', 'items', 'deliveryAddress']);

        if (! $order->customer) {
            return;
        }

        // Send in-app database notification
        $order->customer->notify(new OrderPlacedNotification($order));

        // Build shared mail data
        $item        = $order->items->first();
        $productName = $item?->product_name_snapshot ?? ($item?->product?->name ?? 'Fuel Product');
        $quantity    = (float) ($item?->quantity ?? 0);
        $status      = method_exists($order->status, 'label')
            ? $order->status->label()
            : ucwords(str_replace('_', ' ', (string) ($order->status?->value ?? 'Pending')));
        $address     = $order->deliveryAddress?->full_address
            ?? implode(', ', array_filter([
                $order->deliveryAddress?->address_line_1,
                $order->deliveryAddress?->city,
                $order->deliveryAddress?->state,
            ])) ?: 'N/A';

        // ── Customer confirmation email ─────────────────────────────
        if ($order->customer->email) {
            try {
                SendEmailJob::dispatch(
                    $order->customer->email,
                    new OrderConfirmationMail(
                        customerName:    $order->customer->name,
                        orderNumber:     $order->order_number ?? $order->id,
                        productName:     $productName,
                        quantity:        $quantity,
                        status:          $status,
                        total:           (float) $order->total_amount,
                        deliveryAddress: $address,
                        orderId:         (string) $order->id,
                    )
                );

                Log::info('[SendOrderConfirmationToCustomer] Customer email queued.', [
                    'order_id' => $order->id,
                    'email'    => $order->customer->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('[SendOrderConfirmationToCustomer] Failed to queue customer email', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        // ── Admin notification email ────────────────────────────────
        $adminEmail = config('fuelcab.notifications.email.admin_email', 'admin@fuelcab.com');

        try {
            SendEmailJob::dispatch(
                $adminEmail,
                new OrderConfirmationMail(
                    customerName:    'Admin',
                    orderNumber:     $order->order_number ?? $order->id,
                    productName:     $productName,
                    quantity:        $quantity,
                    status:          $status,
                    total:           (float) $order->total_amount,
                    deliveryAddress: $address,
                    orderId:         (string) $order->id,
                )
            );

            Log::info('[SendOrderConfirmationToCustomer] Admin email queued.', [
                'order_id'    => $order->id,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SendOrderConfirmationToCustomer] Failed to queue admin email', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
