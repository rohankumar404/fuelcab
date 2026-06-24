<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Gateways\Contracts\PaymentGatewayInterface;

class RazorpayGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {}

    public function initiate(array $payload): array
    {
        // TODO: Implement Razorpay order creation via SDK.
        return [];
    }

    public function verify(array $payload): bool
    {
        // TODO: Implement HMAC signature verification.
        return false;
    }

    public function refund(array $payload): array
    {
        // TODO: Implement Razorpay refund API call.
        return [];
    }

    public function gatewayName(): string
    {
        return 'razorpay';
    }
}
