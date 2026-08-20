<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Exceptions\PaymentFailedException;
use App\Modules\Payment\Gateways\PaymentGatewayFactory;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Facades\Log;

class RefundPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayFactory $gatewayFactory
    ) {}

    public function execute(string $paymentId, ?float $amount = null, string $gatewayName = 'razorpay'): array
    {
        $payment = Payment::findOrFail($paymentId);

        if ($payment->status !== 'completed') {
            throw new PaymentFailedException('Only completed payments can be refunded.');
        }

        try {
            $gateway = $this->gatewayFactory->make($gatewayName);
            $refundData = $gateway->refund([
                'payment_id' => $payment->gateway_transaction_id,
                'amount' => $amount,
            ]);

            // Update status
            $payment->update([
                'status' => 'refunded',
            ]);

            Log::info('[RefundPaymentAction] Refund completed successfully', [
                'payment_id' => $paymentId,
                'refund_id' => $refundData['id'],
                'amount' => $amount ?? $payment->amount,
            ]);

            return $refundData;

        } catch (\Throwable $e) {
            Log::error('[RefundPaymentAction] Failed to execute refund', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw new PaymentFailedException('Refund failed: '.$e->getMessage());
        }
    }
}
