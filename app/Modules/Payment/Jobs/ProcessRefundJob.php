<?php

declare(strict_types=1);

namespace App\Modules\Payment\Jobs;

use App\Models\Refund;
use App\Modules\Payment\Gateways\PaymentGatewayFactory;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRefundJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public function __construct(private readonly string $refundId)
    {
        $this->queue = 'high';
    }

    public function handle(PaymentGatewayFactory $gatewayFactory, WalletService $walletService): void
    {
        $refund = Refund::with(['payment.order'])->find($this->refundId);

        if (! $refund) {
            Log::warning('[ProcessRefundJob] Refund record not found.', ['refund_id' => $this->refundId]);

            return;
        }

        if ($refund->status !== 'pending') {
            Log::info('[ProcessRefundJob] Refund already processed.', [
                'refund_id' => $this->refundId,
                'status' => $refund->status,
            ]);

            return;
        }

        $payment = $refund->payment;

        if (! $payment) {
            Log::error('[ProcessRefundJob] Associated payment not found for refund.', ['refund_id' => $this->refundId]);
            $refund->update(['status' => 'failed']);

            return;
        }

        try {
            DB::transaction(function () use ($refund, $payment, $gatewayFactory, $walletService): void {
                if ($payment->gateway === 'wallet') {
                    // Refund to customer wallet
                    $walletService->credit(
                        userId: $payment->order->customer_id,
                        amount: (float) $refund->amount,
                        description: "Refund for order #{$payment->order->order_number}",
                        referenceId: $refund->id,
                        referenceType: 'refund'
                    );

                    $refund->update([
                        'status' => 'processed',
                        'gateway_refund_id' => 'REF-WAL-'.strtoupper(str_replace('-', '', $refund->id)),
                        'processed_at' => now(),
                    ]);
                } else {
                    // Process external gateway refund
                    $gateway = $gatewayFactory->make($payment->gateway);

                    $gatewayResponse = $gateway->refund([
                        'payment_intent_id' => $payment->gateway_payment_id,
                        'payment_id' => $payment->gateway_payment_id,
                        'amount' => (float) $refund->amount,
                    ]);

                    $refund->update([
                        'status' => 'processed',
                        'gateway_refund_id' => $gatewayResponse['id'] ?? null,
                        'processed_at' => now(),
                    ]);
                }

                Log::info('[ProcessRefundJob] Refund processed successfully.', [
                    'refund_id' => $refund->id,
                    'payment_id' => $payment->id,
                    'amount' => $refund->amount,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('[ProcessRefundJob] Refund processing failed.', [
                'refund_id' => $this->refundId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[ProcessRefundJob] Refund job failed permanently.', [
            'refund_id' => $this->refundId,
            'error' => $exception->getMessage(),
        ]);

        Refund::where('id', $this->refundId)->update(['status' => 'failed']);
    }
}
