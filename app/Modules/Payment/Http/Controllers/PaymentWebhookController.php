<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle incoming Razorpay Webhooks.
     *
     * Route: POST /api/v1/payments/webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $secret    = config('fuelcab.payment.webhook.secret', '');
        $payload   = $request->getContent();

        if (empty($signature)) {
            Log::warning('[PaymentWebhook] Missing X-Razorpay-Signature header.');
            return response()->json(['error' => 'Missing signature'], 400);
        }

        // Verify webhook signature locally using HMAC-SHA256
        $expected = hash_hmac('sha256', $payload, $secret);
        if (! hash_equals($expected, $signature)) {
            Log::warning('[PaymentWebhook] Webhook signature verification failed.');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $eventData = json_decode($payload, true) ?? [];
        $event     = $eventData['event'] ?? '';

        Log::info('[PaymentWebhook] Received Razorpay webhook event: ' . $event);

        switch ($event) {
            case 'payment.captured':
                $this->handlePaymentCaptured($eventData);
                break;

            case 'payment.failed':
                $this->handlePaymentFailed($eventData);
                break;

            case 'refund.processed':
                $this->handleRefundProcessed($eventData);
                break;

            default:
                Log::debug('[PaymentWebhook] Unhandled event type: ' . $event);
                break;
        }

        return response()->json(['received' => true]);
    }

    protected function handlePaymentCaptured(array $eventData): void
    {
        $paymentPayload = $eventData['payload']['payment']['entity'] ?? [];
        $gatewayOrderId = $paymentPayload['order_id'] ?? '';
        $paymentId      = $paymentPayload['id'] ?? '';

        if (empty($gatewayOrderId) || empty($paymentId)) {
            return;
        }

        $payment = Payment::where('gateway_transaction_id', $gatewayOrderId)
            ->orWhere('gateway_transaction_id', $paymentId)
            ->first();

        if ($payment && $payment->status !== 'completed') {
            $payment->update([
                'status'                 => 'completed',
                'gateway_transaction_id' => $paymentId,
                'paid_at'                => now(),
            ]);

            // Transition the order status from Pending to Accepted on successful payment
            $order = $payment->order;
            if ($order && $order->status === \App\Modules\Order\Enums\OrderStatus::Pending) {
                $order->update([
                    'status' => \App\Modules\Order\Enums\OrderStatus::Accepted,
                ]);

                event(new \App\Modules\Order\Events\OrderAccepted($order));
            }

            event(new PaymentVerified($payment));

            Log::info('[PaymentWebhook] Webhook processed payment.captured successfully.', [
                'payment_id' => $payment->id,
                'order_id'   => $payment->order_id,
            ]);
        }
    }

    protected function handlePaymentFailed(array $eventData): void
    {
        $paymentPayload = $eventData['payload']['payment']['entity'] ?? [];
        $gatewayOrderId = $paymentPayload['order_id'] ?? '';
        $paymentId      = $paymentPayload['id'] ?? '';
        $errorMessage   = $paymentPayload['error_description'] ?? 'Webhook reported payment failure';

        if (empty($gatewayOrderId)) {
            return;
        }

        $payment = Payment::where('gateway_transaction_id', $gatewayOrderId)
            ->orWhere('gateway_transaction_id', $paymentId)
            ->first();

        if ($payment && $payment->status !== 'completed') {
            $payment->update([
                'status'        => 'failed',
                'error_message' => $errorMessage,
            ]);

            event(new PaymentFailed($payment, $errorMessage));

            Log::info('[PaymentWebhook] Webhook processed payment.failed successfully.', [
                'payment_id' => $payment->id,
                'error'      => $errorMessage,
            ]);
        }
    }

    protected function handleRefundProcessed(array $eventData): void
    {
        $refundPayload = $eventData['payload']['refund']['entity'] ?? [];
        $paymentId     = $refundPayload['payment_id'] ?? '';

        if (empty($paymentId)) {
            return;
        }

        $payment = Payment::where('gateway_transaction_id', $paymentId)->first();

        if ($payment && $payment->status !== 'refunded') {
            $payment->update([
                'status' => 'refunded',
            ]);

            Log::info('[PaymentWebhook] Webhook processed refund.processed successfully.', [
                'payment_id' => $payment->id,
            ]);
        }
    }
}
