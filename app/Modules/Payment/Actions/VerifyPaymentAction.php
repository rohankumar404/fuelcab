<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Exceptions\PaymentFailedException;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderAccepted;
use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Gateways\PaymentGatewayFactory;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Facades\Log;

class VerifyPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayFactory $gatewayFactory
    ) {}

    public function execute(array $payload, string $gatewayName = 'razorpay'): bool
    {
        $gatewayOrderId = (string) ($payload['razorpay_order_id'] ?? '');
        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');

        if (empty($gatewayOrderId) || empty($paymentId)) {
            throw new PaymentFailedException('Invalid verification payload.');
        }

        // Find the payment record associated with this gateway order ID
        $payment = Payment::where('gateway_transaction_id', $gatewayOrderId)
            ->firstOrFail();

        try {
            $gateway = $this->gatewayFactory->make($gatewayName);
            $verified = $gateway->verify($payload);

            if ($verified) {
                $payment->update([
                    'status' => 'completed',
                    'gateway_transaction_id' => $paymentId, // update to actual transaction reference
                    'paid_at' => now(),
                ]);

                // Transition the order status from Pending to Accepted on successful payment
                $order = $payment->order;
                if ($order && $order->status === OrderStatus::Pending) {
                    $order->update([
                        'status' => OrderStatus::Accepted,
                    ]);

                    event(new OrderAccepted($order));
                }

                event(new PaymentVerified($payment));

                Log::info('[VerifyPaymentAction] Payment verified and completed successfully.', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                ]);

                return true;
            }

            // If not verified
            $payment->update([
                'status' => 'failed',
                'error_message' => 'Signature verification failed.',
            ]);

            event(new PaymentFailed($payment, 'Signature verification failed.'));

            return false;

        } catch (\Throwable $e) {
            $payment->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            event(new PaymentFailed($payment, $e->getMessage()));

            Log::error('[VerifyPaymentAction] Exception during payment verification', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
