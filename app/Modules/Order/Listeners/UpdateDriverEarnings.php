<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Notifications\OrderDeliveredNotification;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateDriverEarnings implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public int $tries = 3;

    public function __construct(private readonly WalletService $walletService) {}

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order->load(['customer', 'driver']);

        // Notify customer of successful delivery
        if ($order->customer) {
            $order->customer->notify(new OrderDeliveredNotification($order));
        }

        // Credit driver earnings to the wallet module
        if ($order->driver_id && $order->driver_earnings > 0) {
            try {
                $this->walletService->credit(
                    userId: $order->driver_id,
                    amount: (float) $order->driver_earnings,
                    description: "Earnings for order #{$order->id}",
                    referenceId: $order->id,
                    referenceType: 'order',
                );

                Log::info('[UpdateDriverEarnings] Driver earnings credited.', [
                    'order_id' => $order->id,
                    'driver_id' => $order->driver_id,
                    'driver_earnings' => $order->driver_earnings,
                ]);
            } catch (Throwable $e) {
                Log::error('[UpdateDriverEarnings] Failed to credit driver earnings.', [
                    'order_id' => $order->id,
                    'driver_id' => $order->driver_id,
                    'error' => $e->getMessage(),
                ]);
                // Re-throw so the queue can retry via $tries
                throw $e;
            }
        } else {
            Log::info('[UpdateDriverEarnings] No driver or zero earnings — skipping credit.', [
                'order_id' => $order->id,
                'driver_id' => $order->driver_id,
            ]);
        }
    }

    public function failed(OrderCompleted $event, Throwable $exception): void
    {
        Log::error('[UpdateDriverEarnings] Job failed permanently.', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
