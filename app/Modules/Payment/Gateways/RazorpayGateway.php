<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Exceptions\PaymentFailedException;
use App\Modules\Payment\Gateways\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayGateway implements PaymentGatewayInterface
{
    protected string $endpoint = 'https://api.razorpay.com/v1';

    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {}

    /**
     * Initiate a Razorpay payment order.
     */
    public function initiate(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $amount  = (float) ($payload['amount'] ?? 0.0);

        if (empty($orderId) || $amount <= 0.0) {
            throw new PaymentFailedException('Invalid order or amount for payment initiation.');
        }

        try {
            // Retry up to 3 times with 100ms delay between attempts on gateway timeouts or failures
            $response = Http::withBasicAuth($this->key, $this->secret)
                ->retry(3, 100)
                ->timeout(15)
                ->post($this->endpoint . '/orders', [
                    'amount'   => (int) round($amount * 100), // amount in paise
                    'currency' => 'INR',
                    'receipt'  => $orderId,
                ]);

            if (! $response->successful()) {
                Log::error('[RazorpayGateway] Order creation failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new PaymentFailedException('Gateway returned an error: ' . $response->body());
            }

            return [
                'id'       => $response->json('id'),
                'amount'   => $amount,
                'currency' => 'INR',
                'receipt'  => $orderId,
            ];

        } catch (\Throwable $e) {
            Log::error('[RazorpayGateway] Exception initiating payment', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            throw new PaymentFailedException('Failed to initiate payment with Razorpay: ' . $e->getMessage());
        }
    }

    /**
     * Verify a Razorpay payment transaction.
     */
    public function verify(array $payload): bool
    {
        $orderId   = (string) ($payload['razorpay_order_id'] ?? '');
        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');
        $signature = (string) ($payload['razorpay_signature'] ?? '');

        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            Log::warning('[RazorpayGateway] Missing verification parameters.');
            return false;
        }

        // 1. Verify signature locally using HMAC-SHA256
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->secret);
        if (! hash_equals($expected, $signature)) {
            Log::warning('[RazorpayGateway] HMAC signature mismatch.', [
                'expected' => $expected,
                'received' => $signature,
            ]);
            return false;
        }

        // 2. Query Razorpay API to check actual capture status
        try {
            $response = Http::withBasicAuth($this->key, $this->secret)
                ->retry(3, 100)
                ->timeout(15)
                ->get($this->endpoint . "/payments/{$paymentId}");

            if (! $response->successful()) {
                Log::error('[RazorpayGateway] Failed to retrieve payment details', [
                    'payment_id' => $paymentId,
                    'status'     => $response->status(),
                ]);
                return false;
            }

            $status = $response->json('status');
            $amount = $response->json('amount');

            // If payment is authorized but not captured, capture it now
            if ($status === 'authorized') {
                $captureResponse = Http::withBasicAuth($this->key, $this->secret)
                    ->retry(3, 100)
                    ->timeout(15)
                    ->post($this->endpoint . "/payments/{$paymentId}/capture", [
                        'amount'   => $amount,
                        'currency' => 'INR',
                    ]);

                if ($captureResponse->successful() && $captureResponse->json('status') === 'captured') {
                    Log::info('[RazorpayGateway] Payment captured successfully via API capture call.', [
                        'payment_id' => $paymentId,
                    ]);
                    return true;
                }

                Log::error('[RazorpayGateway] Failed to capture authorized payment', [
                    'payment_id' => $paymentId,
                    'response'   => $captureResponse->body(),
                ]);
                return false;
            }

            return $status === 'captured';

        } catch (\Throwable $e) {
            Log::error('[RazorpayGateway] Exception during payment verification details retrieval', [
                'payment_id' => $paymentId,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process a Razorpay payment refund.
     */
    public function refund(array $payload): array
    {
        $paymentId = (string) ($payload['payment_id'] ?? '');
        $amount    = isset($payload['amount']) ? (float) $payload['amount'] : null;

        if (empty($paymentId)) {
            throw new PaymentFailedException('Invalid payment ID for refund request.');
        }

        try {
            $body = [];
            if ($amount !== null && $amount > 0.0) {
                $body['amount'] = (int) round($amount * 100); // convert to paise
            }

            $response = Http::withBasicAuth($this->key, $this->secret)
                ->retry(3, 100)
                ->timeout(15)
                ->post($this->endpoint . "/payments/{$paymentId}/refund", $body);

            if (! $response->successful()) {
                Log::error('[RazorpayGateway] Refund execution failed', [
                    'payment_id' => $paymentId,
                    'status'     => $response->status(),
                    'response'   => $response->body(),
                ]);
                throw new PaymentFailedException('Gateway returned refund error: ' . $response->body());
            }

            return [
                'id'         => $response->json('id'),
                'payment_id' => $paymentId,
                'amount'     => $response->json('amount') / 100, // back to INR
                'status'     => $response->json('status'),
            ];

        } catch (\Throwable $e) {
            Log::error('[RazorpayGateway] Exception processing refund', [
                'payment_id' => $paymentId,
                'error'      => $e->getMessage(),
            ]);
            throw new PaymentFailedException('Failed to execute refund with Razorpay: ' . $e->getMessage());
        }
    }

    public function gatewayName(): string
    {
        return 'razorpay';
    }
}
