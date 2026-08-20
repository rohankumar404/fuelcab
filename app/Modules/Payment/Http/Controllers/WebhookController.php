<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Gateways\PaymentGatewayFactory;
use App\Modules\Payment\Gateways\RazorpayGateway;
use App\Modules\Payment\Gateways\StripeGateway;
use App\Modules\Payment\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private readonly PaymentGatewayFactory $gatewayFactory) {}

    /**
     * Handle Stripe Webhook calls.
     *
     * Expects: POST /api/v1/payments/webhooks/stripe
     * Header:  Stripe-Signature: ...
     */
    public function stripe(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        /** @var StripeGateway $gateway */
        $gateway = $this->gatewayFactory->make('stripe');

        $isValid = $gateway->verify([
            'stripe_signature' => $sigHeader,
            'raw_body' => $rawBody,
        ]);

        if (! $isValid) {
            Log::warning('[WebhookController] Stripe signature verification failed.');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = json_decode($rawBody, true) ?? [];
        $eventType = (string) ($payload['type'] ?? '');

        Log::info('[WebhookController] Stripe webhook received.', ['type' => $eventType]);

        match ($eventType) {
            'payment_intent.succeeded' => $this->handleStripePaymentSucceeded($payload),
            'charge.failed' => $this->handleStripeChargeFailed($payload),
            default => null, // silently ignore unknown event types
        };

        return response()->json(['received' => true]);
    }

    /**
     * Handle Razorpay Webhook calls.
     *
     * Expects: POST /api/v1/payments/webhooks/razorpay
     * Header:  X-Razorpay-Signature: ...
     */
    public function razorpay(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $sigHeader = $request->header('X-Razorpay-Signature', '');

        /** @var RazorpayGateway $gateway */
        $gateway = $this->gatewayFactory->make('razorpay');

        // Razorpay webhook signature: HMAC-SHA256 of raw body with webhook secret
        $webhookSecret = (string) config('services.razorpay.webhook_secret', '');
        $expectedSig = hash_hmac('sha256', $rawBody, $webhookSecret);

        if (! hash_equals($expectedSig, $sigHeader)) {
            Log::warning('[WebhookController] Razorpay signature verification failed.');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = json_decode($rawBody, true) ?? [];
        $eventType = (string) ($payload['event'] ?? '');

        Log::info('[WebhookController] Razorpay webhook received.', ['event' => $eventType]);

        match ($eventType) {
            'payment.captured' => $this->handleRazorpayPaymentCaptured($payload),
            'payment.failed' => $this->handleRazorpayPaymentFailed($payload),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private Stripe event handlers
    // ─────────────────────────────────────────────────────────────────────────

    private function handleStripePaymentSucceeded(array $payload): void
    {
        $intentData = $payload['data']['object'] ?? [];
        $orderId = $intentData['metadata']['order_id'] ?? null;
        $amount = isset($intentData['amount']) ? $intentData['amount'] / 100 : 0;

        if (! $orderId) {
            return;
        }

        $payment = Payment::where('order_id', $orderId)
            ->where('gateway', 'stripe')
            ->first();

        if ($payment && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'gateway_payment_id' => $intentData['id'] ?? null,
                'paid_at' => now(),
            ]);

            event(new PaymentVerified($payment));

            Log::info('[WebhookController] Stripe payment marked as paid.', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);
        }
    }

    private function handleStripeChargeFailed(array $payload): void
    {
        $chargeData = $payload['data']['object'] ?? [];
        $orderId = $chargeData['metadata']['order_id'] ?? null;

        if (! $orderId) {
            return;
        }

        Payment::where('order_id', $orderId)
            ->where('gateway', 'stripe')
            ->where('status', 'pending')
            ->update(['status' => 'failed']);

        Log::warning('[WebhookController] Stripe charge failed.', ['order_id' => $orderId]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private Razorpay event handlers
    // ─────────────────────────────────────────────────────────────────────────

    private function handleRazorpayPaymentCaptured(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $orderId = $paymentData['receipt'] ?? null;

        if (! $orderId) {
            return;
        }

        $payment = Payment::where('order_id', $orderId)
            ->where('gateway', 'razorpay')
            ->first();

        if ($payment && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'gateway_payment_id' => $paymentData['id'] ?? null,
                'paid_at' => now(),
            ]);

            event(new PaymentVerified($payment));

            Log::info('[WebhookController] Razorpay payment captured.', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
            ]);
        }
    }

    private function handleRazorpayPaymentFailed(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $orderId = $paymentData['receipt'] ?? null;

        if (! $orderId) {
            return;
        }

        Payment::where('order_id', $orderId)
            ->where('gateway', 'razorpay')
            ->where('status', 'pending')
            ->update(['status' => 'failed']);

        Log::warning('[WebhookController] Razorpay payment failed.', ['order_id' => $orderId]);
    }
}
