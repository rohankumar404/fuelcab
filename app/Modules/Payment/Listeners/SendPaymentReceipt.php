<?php

declare(strict_types=1);

namespace App\Modules\Payment\Listeners;

use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Notifications\PaymentSuccessfulNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentReceipt implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(PaymentVerified $event): void
    {
        try {
            $payment = $event->payment->loadMissing(['order.customer']);
            $customer = $payment->order?->customer ?? null;

            if ($customer) {
                $customer->notify(new PaymentSuccessfulNotification($payment));
            } else {
                Log::warning('[SendPaymentReceipt] Could not resolve customer for payment notification.', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[SendPaymentReceipt] Failed to notify customer of payment receipt', [
                'payment_id' => $event->payment->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
