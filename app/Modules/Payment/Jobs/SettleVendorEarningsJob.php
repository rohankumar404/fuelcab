<?php

declare(strict_types=1);

namespace App\Modules\Payment\Jobs;

use App\Models\Settlement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SettleVendorEarningsJob
 *
 * Aggregates all paid orders for a vendor into a Settlement record.
 * Dispatched by TriggerPaymentSettlement after an order is completed.
 */
class SettleVendorEarningsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private readonly string $orderId,
        private readonly string $vendorId,
        private readonly float $grossAmount,
    ) {
        $this->queue = 'default';
    }

    public function handle(): void
    {
        $commissionRate = (float) config('fuelcab.payment.commission_rate', 0.10);
        $commission = round($this->grossAmount * $commissionRate, 2);
        $netPayable = round($this->grossAmount - $commission, 2);

        DB::transaction(function () use ($commission, $netPayable): void {
            Settlement::create([
                'vendor_id' => $this->vendorId,
                'gross_amount' => $this->grossAmount,
                'commission_amount' => $commission,
                'net_payable' => $netPayable,
                'status' => 'pending',
                'payout_reference' => null,
                'adjustments' => 0.00,
            ]);
        });

        Log::info('[SettleVendorEarningsJob] Settlement created.', [
            'order_id' => $this->orderId,
            'vendor_id' => $this->vendorId,
            'gross' => $this->grossAmount,
            'commission' => $commission,
            'net_payable' => $netPayable,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[SettleVendorEarningsJob] Settlement creation failed permanently.', [
            'order_id' => $this->orderId,
            'vendor_id' => $this->vendorId,
            'error' => $exception->getMessage(),
        ]);
    }
}
