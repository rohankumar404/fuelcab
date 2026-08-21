<?php

declare(strict_types=1);

namespace App\Modules\Order\Jobs;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\OrderConfirmationMail;
use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderReceiptJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(private readonly string $orderId)
    {
        $this->queue = 'default';
    }

    public function handle(): void
    {
        $order = Order::with(['customer', 'items', 'deliveryAddress'])->find($this->orderId);

        if (! $order) {
            Log::warning('[SendOrderReceiptJob] Order not found.', ['order_id' => $this->orderId]);

            return;
        }

        $customer = $order->customer;
        $item     = $order->items->first();
        $productName = $item?->product_name_snapshot ?? ($item?->product?->name ?? 'Fuel Product');
        $quantity    = (float) ($item?->quantity ?? 0);
        $status      = method_exists($order->status, 'label') ? $order->status->label() : ucwords(str_replace('_', ' ', (string) $order->status?->value ?? 'Pending'));
        $address     = $order->deliveryAddress?->full_address
            ?? implode(', ', array_filter([
                $order->deliveryAddress?->address_line_1,
                $order->deliveryAddress?->city,
                $order->deliveryAddress?->state,
            ])) ?: 'N/A';

        $mail = new \App\Modules\Notification\Mail\OrderConfirmationMail(
            customerName:    $customer?->name ?? 'Customer',
            orderNumber:     $order->order_number ?? $order->id,
            productName:     $productName,
            quantity:        $quantity,
            status:          $status,
            total:           (float) $order->total_amount,
            deliveryAddress: $address,
            orderId:         (string) $order->id,
        );

        // ── Customer email ──────────────────────────────────────────
        if ($customer && $customer->email) {
            SendEmailJob::dispatch($customer->email, $mail);

            Log::info('[SendOrderReceiptJob] Customer receipt dispatched.', [
                'order_id' => $this->orderId,
                'email'    => $customer->email,
            ]);
        } else {
            Log::warning('[SendOrderReceiptJob] Customer has no email — skipping customer receipt.', [
                'order_id'    => $this->orderId,
                'customer_id' => $order->customer_id,
            ]);
        }

        // ── Admin notification email ────────────────────────────────
        $adminEmail = config('fuelcab.notifications.email.admin_email', 'admin@fuelcab.com');

        $adminMail = new \App\Modules\Notification\Mail\OrderConfirmationMail(
            customerName:    'Admin',
            orderNumber:     $order->order_number ?? $order->id,
            productName:     $productName,
            quantity:        $quantity,
            status:          $status,
            total:           (float) $order->total_amount,
            deliveryAddress: $address,
            orderId:         (string) $order->id,
        );

        SendEmailJob::dispatch($adminEmail, $adminMail);

        Log::info('[SendOrderReceiptJob] Admin notification dispatched.', [
            'order_id'    => $this->orderId,
            'admin_email' => $adminEmail,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[SendOrderReceiptJob] Failed to send order receipt.', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
