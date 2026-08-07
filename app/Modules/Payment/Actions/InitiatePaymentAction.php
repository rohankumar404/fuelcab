<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Exceptions\PaymentFailedException;
use App\Modules\Payment\DTOs\InitiatePaymentDTO;
use App\Modules\Payment\Events\PaymentInitiated;
use App\Modules\Payment\Gateways\PaymentGatewayFactory;
use App\Modules\Payment\Models\Payment;
use App\Modules\Order\Models\Order;

class InitiatePaymentAction
{
    public function __construct(
        private readonly PaymentGatewayFactory $gatewayFactory
    ) {}

    public function execute(string $orderId, string $gatewayName, float $amount, ?string $currency = 'INR'): InitiatePaymentDTO
    {
        $order = Order::findOrFail($orderId);

        // Check if there is already a completed payment for this order
        $existingCompleted = Payment::where('order_id', $orderId)
            ->where('status', 'completed')
            ->exists();

        if ($existingCompleted) {
            throw new PaymentFailedException('This order has already been paid for.');
        }

        // Create a local payment record in pending state
        $payment = Payment::create([
            'order_id'        => $orderId,
            'payment_gateway' => $gatewayName,
            'amount'          => $amount,
            'currency'        => $currency ?? 'INR',
            'status'          => 'pending',
        ]);

        try {
            $gateway = $this->gatewayFactory->make($gatewayName);
            $gatewayData = $gateway->initiate([
                'order_id' => $orderId,
                'amount'   => $amount,
            ]);

            // Save the gateway order ID to our local payment record
            $payment->update([
                'gateway_transaction_id' => $gatewayData['id'],
            ]);

            event(new PaymentInitiated($payment));

            return InitiatePaymentDTO::fromArray([
                'payment_id'       => $payment->id,
                'gateway_order_id' => $gatewayData['id'],
                'amount'           => $amount,
                'currency'         => $currency ?? 'INR',
                'gateway'          => $gatewayName,
            ]);

        } catch (\Throwable $e) {
            $payment->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw new PaymentFailedException('Payment initiation failed: ' . $e->getMessage());
        }
    }
}
