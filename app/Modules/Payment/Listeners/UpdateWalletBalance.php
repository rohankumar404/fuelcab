<?php

declare(strict_types=1);

namespace App\Modules\Payment\Listeners;

use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateWalletBalance implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public int $tries = 3;

    public function __construct(private readonly WalletService $walletService) {}

    public function handle(PaymentVerified $event): void
    {
        $payment = $event->payment;

        if (! $payment->order) {
            Log::warning('[UpdateWalletBalance] Payment has no associated order.', ['payment_id' => $payment->id]);

            return;
        }

        $order = $payment->order;

        if (! $order->customer_id) {
            return;
        }

        // If the payment method was wallet, the balance was already debited at checkout.
        // This listener credits cashback or refunds if applicable (future extension point).
        // For now, log the verified payment for audit purposes.
        Log::info('[UpdateWalletBalance] Payment verified — wallet audit complete.', [
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'amount' => $payment->amount,
            'method' => $payment->payment_method ?? 'unknown',
        ]);
    }

    public function failed(PaymentVerified $event, Throwable $exception): void
    {
        Log::error('[UpdateWalletBalance] Failed to process wallet balance update.', [
            'payment_id' => $event->payment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
