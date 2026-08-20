<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Payment\Jobs\SettleVendorEarningsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TriggerPaymentSettlement implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public int $tries = 3;

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        if (! $order->vendor_id || ! $order->total_amount) {
            Log::warning('[TriggerPaymentSettlement] Skipping settlement — missing vendor or amount.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        // Dispatch vendor settlement job on the default queue
        SettleVendorEarningsJob::dispatch(
            orderId: $order->id,
            vendorId: $order->vendor_id,
            grossAmount: (float) $order->total_amount,
        );

        Log::info('[TriggerPaymentSettlement] Settlement job dispatched.', [
            'order_id' => $order->id,
            'vendor_id' => $order->vendor_id,
            'total_amount' => $order->total_amount,
        ]);
    }
}
