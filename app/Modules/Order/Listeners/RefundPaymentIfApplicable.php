<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Notifications\OrderCancelledNotification;
use App\Modules\Wallet\Models\Wallet;
use App\Modules\Wallet\Models\WalletTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
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

        // Issue refund to customer's wallet
        DB::transaction(function () use ($order, $event) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $order->customer_id],
                [
                    'balance'  => 0.00,
                    'currency' => 'INR',
                ]
            );

            $balanceBefore = (float) $wallet->balance;
            $refundAmount  = (float) $order->total_amount;
            $balanceAfter  = $balanceBefore + $refundAmount;

            $wallet->update(['balance' => $balanceAfter]);

            WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'credit',
                'amount'         => $refundAmount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'description'    => "Refund for cancelled order #{$order->order_number}",
                'reference_id'   => $order->id,
                'reference_type' => 'refund',
            ]);

            Log::info('OrderModule: Order cancelled — refund processed to wallet', [
                'order_id'       => $order->id,
                'customer_id'    => $order->customer_id,
                'refund_amount'  => $refundAmount,
                'wallet_balance' => $balanceAfter,
            ]);
        });
    }
}
