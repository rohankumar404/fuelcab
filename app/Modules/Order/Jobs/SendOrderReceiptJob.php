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
        $order = Order::with(['customer', 'items'])->find($this->orderId);

        if (! $order) {
            Log::warning('[SendOrderReceiptJob] Order not found.', ['order_id' => $this->orderId]);

            return;
        }

        $customer = $order->customer;

        if (! $customer || ! $customer->email) {
            Log::warning('[SendOrderReceiptJob] Customer has no email — skipping receipt.', [
                'order_id' => $this->orderId,
                'customer_id' => $order->customer_id,
            ]);

            return;
        }

        SendEmailJob::dispatch($customer->email, new OrderConfirmationMail($order));

        Log::info('[SendOrderReceiptJob] Order receipt dispatched.', [
            'order_id' => $this->orderId,
            'email' => $customer->email,
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
