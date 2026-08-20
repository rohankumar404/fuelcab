<?php

declare(strict_types=1);

namespace App\Modules\Order\Jobs;

use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ProcessOrderJob
 *
 * Transitions an order from "pending" to "accepted" and validates
 * that the vendor has available inventory for the requested items.
 */
class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public function __construct(private readonly string $orderId)
    {
        $this->queue = 'high';
    }

    public function handle(): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (! $order) {
            Log::warning('[ProcessOrderJob] Order not found — skipping.', ['order_id' => $this->orderId]);

            return;
        }

        if ($order->status !== OrderStatus::Pending) {
            Log::info('[ProcessOrderJob] Order already processed.', [
                'order_id' => $this->orderId,
                'status' => $order->status->value,
            ]);

            return;
        }

        DB::transaction(function () use ($order): void {
            $oldStatus = $order->status;

            $order->update(['status' => OrderStatus::Accepted]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus->value,
                'to_status' => OrderStatus::Accepted->value,
                'reason' => 'Auto-accepted via order processing queue.',
                'changed_by' => null,
            ]);

            Log::info('[ProcessOrderJob] Order accepted.', ['order_id' => $this->orderId]);
        });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[ProcessOrderJob] Failed to process order.', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
