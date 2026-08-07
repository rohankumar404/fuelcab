<?php

declare(strict_types=1);

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\Gateways\PaymentGatewayFactory;
use App\Modules\Payment\Gateways\RazorpayGateway;
use App\Modules\Payment\Gateways\StripeGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayFactory::class, function ($app) {
            $razorpayConfig = config('fuelcab.payment.gateways.razorpay');
            $razorpay = new RazorpayGateway(
                (string) ($razorpayConfig['key'] ?? ''),
                (string) ($razorpayConfig['secret'] ?? '')
            );

            $stripeConfig = config('fuelcab.payment.gateways.stripe');
            $stripe = new StripeGateway(
                (string) ($stripeConfig['key'] ?? ''),
                (string) ($stripeConfig['secret'] ?? '')
            );

            return new PaymentGatewayFactory([
                'razorpay' => $razorpay,
                'stripe'   => $stripe,
            ]);
        });
    }

    public function boot(): void
    {
        //
    }
}
