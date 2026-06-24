<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Gateways\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * @param  array<string, PaymentGatewayInterface>  $gateways
     */
    public function __construct(
        private readonly array $gateways = [],
    ) {}

    /**
     * Resolve a gateway by name.
     *
     * @throws InvalidArgumentException
     */
    public function make(string $gateway): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$gateway])) {
            throw new InvalidArgumentException("Payment gateway [{$gateway}] is not registered.");
        }

        return $this->gateways[$gateway];
    }

    /**
     * Resolve the configured default gateway.
     */
    public function default(): PaymentGatewayInterface
    {
        $default = config('fuelcab.payment.default_gateway', 'razorpay');

        return $this->make($default);
    }
}
