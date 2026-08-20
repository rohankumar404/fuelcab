<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Exceptions\PaymentFailedException;
use App\Modules\Payment\Gateways\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    protected string $endpoint = 'https://api.stripe.com/v1';

    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {}

    /**
     * Create a Stripe PaymentIntent and return client_secret for the frontend.
     */
    public function initiate(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (float) ($payload['amount'] ?? 0.0);

        if (empty($orderId) || $amount <= 0.0) {
            throw new PaymentFailedException('Invalid order or amount for Stripe payment initiation.');
        }

        try {
            $response = Http::withToken($this->secret)
                ->asForm()
                ->retry(3, 100)
                ->timeout(15)
                ->post($this->endpoint.'/payment_intents', [
                    'amount' => (int) round($amount * 100), // amount in paise/cents
                    'currency' => 'inr',
                    'metadata[order_id]' => $orderId,
                    'automatic_payment_methods[enabled]' => 'true',
                ]);

            if (! $response->successful()) {
                Log::error('[StripeGateway] PaymentIntent creation failed.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new PaymentFailedException('Stripe returned an error: '.$response->body());
            }

            return [
                'id' => $response->json('id'),
                'client_secret' => $response->json('client_secret'),
                'amount' => $amount,
                'currency' => 'INR',
                'order_id' => $orderId,
            ];

        } catch (\Throwable $e) {
            Log::error('[StripeGateway] Exception initiating PaymentIntent.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw new PaymentFailedException('Failed to initiate Stripe payment: '.$e->getMessage());
        }
    }

    /**
     * Verify a Stripe webhook event signature using the webhook secret.
     *
     * $payload keys expected:
     *  - stripe_signature : value of the Stripe-Signature HTTP header
     *  - raw_body        : raw request body string
     */
    public function verify(array $payload): bool
    {
        $signature = (string) ($payload['stripe_signature'] ?? '');
        $rawBody = (string) ($payload['raw_body'] ?? '');
        $webhookKey = config('services.stripe.webhook_secret', '');

        if (empty($signature) || empty($rawBody) || empty($webhookKey)) {
            Log::warning('[StripeGateway] Missing signature verification parameters.');

            return false;
        }

        // Parse timestamp and v1 signature from header
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$k] = $v;
        }

        $timestamp = (string) ($parts['t'] ?? '');
        $v1Sig = (string) ($parts['v1'] ?? '');

        if (empty($timestamp) || empty($v1Sig)) {
            return false;
        }

        // Reject signatures older than 5 minutes to prevent replay attacks
        if (abs(time() - (int) $timestamp) > 300) {
            Log::warning('[StripeGateway] Stripe signature timestamp too old — possible replay.');

            return false;
        }

        $expectedSig = hash_hmac('sha256', $timestamp.'.'.$rawBody, $webhookKey);

        return hash_equals($expectedSig, $v1Sig);
    }

    /**
     * Issue a full or partial Stripe refund for a PaymentIntent.
     */
    public function refund(array $payload): array
    {
        $paymentIntentId = (string) ($payload['payment_intent_id'] ?? '');
        $amount = isset($payload['amount']) ? (float) $payload['amount'] : null;

        if (empty($paymentIntentId)) {
            throw new PaymentFailedException('Invalid PaymentIntent ID for Stripe refund.');
        }

        try {
            $body = ['payment_intent' => $paymentIntentId];

            if ($amount !== null && $amount > 0.0) {
                $body['amount'] = (int) round($amount * 100);
            }

            $response = Http::withToken($this->secret)
                ->asForm()
                ->retry(3, 100)
                ->timeout(15)
                ->post($this->endpoint.'/refunds', $body);

            if (! $response->successful()) {
                Log::error('[StripeGateway] Refund failed.', [
                    'payment_intent_id' => $paymentIntentId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new PaymentFailedException('Stripe refund error: '.$response->body());
            }

            return [
                'id' => $response->json('id'),
                'payment_intent_id' => $paymentIntentId,
                'amount' => $response->json('amount') / 100,
                'status' => $response->json('status'),
                'currency' => strtoupper((string) $response->json('currency')),
            ];

        } catch (\Throwable $e) {
            Log::error('[StripeGateway] Exception processing refund.', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw new PaymentFailedException('Failed to execute Stripe refund: '.$e->getMessage());
        }
    }

    public function gatewayName(): string
    {
        return 'stripe';
    }
}
