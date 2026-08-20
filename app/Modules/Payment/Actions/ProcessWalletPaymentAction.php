<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Modules\Order\Models\Order;
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Models\Payment;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWalletPaymentAction
{
    public function __construct(private readonly WalletService $walletService) {}

    /**
     * Process order payment using user's wallet balance.
     */
    public function execute(string $orderId, string $userId): Payment
    {
        return DB::transaction(function () use ($orderId, $userId): Payment {
            $order = Order::findOrFail($orderId);
            $amount = (float) $order->total_amount;

            // Debit from wallet
            $this->walletService->debit(
                userId: $userId,
                amount: $amount,
                description: "Payment for order #{$order->order_number}",
                referenceId: $orderId,
                referenceType: 'order'
            );

            // Create/update payment record
            $payment = Payment::updateOrCreate(
                ['order_id' => $orderId, 'gateway' => 'wallet'],
                [
                    'amount' => $amount,
                    'status' => 'paid',
                    'gateway_payment_id' => 'WAL-'.strtoupper(str_replace('-', '', $orderId)),
                    'paid_at' => now(),
                ]
            );

            Log::info('[ProcessWalletPaymentAction] Wallet payment processed.', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

            // Fire PaymentVerified event to transition order status etc.
            event(new PaymentVerified($payment));

            return $payment;
        });
    }
}
