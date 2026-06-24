<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment and return the gateway's order/session data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function initiate(array $payload): array;

    /**
     * Verify a payment using gateway-provided signature / token.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verify(array $payload): bool;

    /**
     * Issue a full or partial refund.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function refund(array $payload): array;

    /**
     * Return the unique gateway identifier (e.g. 'razorpay').
     */
    public function gatewayName(): string;
}
