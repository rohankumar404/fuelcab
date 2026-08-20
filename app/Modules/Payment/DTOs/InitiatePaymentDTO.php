<?php

declare(strict_types=1);

namespace App\Modules\Payment\DTOs;

use App\DTOs\BaseDTO;

final class InitiatePaymentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $gatewayOrderId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $gateway
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            paymentId: (string) ($data['payment_id'] ?? ''),
            gatewayOrderId: (string) ($data['gateway_order_id'] ?? ''),
            amount: (float) ($data['amount'] ?? 0.0),
            currency: (string) ($data['currency'] ?? 'INR'),
            gateway: (string) ($data['gateway'] ?? 'razorpay')
        );
    }

    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'gateway_order_id' => $this->gatewayOrderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'gateway' => $this->gateway,
        ];
    }
}
