<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Jobs\SendOrderReceiptJob;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;

class CompleteOrderAction
{
    /**
     * Mark an order as delivered/completed and fire post-completion events.
     *
     * @throws \DomainException if the order is not in a deliverable state.
     */
    public function execute(string $orderId, ?string $completedBy = null): Order
    {
        return DB::transaction(function () use ($orderId, $completedBy): Order {
            $order = Order::findOrFail($orderId);

            if (! $order->status->canTransitionTo(OrderStatus::Delivered)) {
                throw new \DomainException(
                    "Cannot complete an order with status '{$order->status->value}'. ".
                    'Only dispatched (out_for_delivery) orders may be completed.'
                );
            }

            $oldStatus = $order->status;

            $order->update([
                'status' => OrderStatus::Delivered,
                'delivered_at' => now(),
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus->value,
                'to_status' => OrderStatus::Delivered->value,
                'reason' => 'Order delivered successfully.',
                'changed_by' => $completedBy,
            ]);

            // Fire event — listeners handle: driver earnings, settlement, push notifications
            event(new OrderCompleted($order));

            // Send receipt email asynchronously
            SendOrderReceiptJob::dispatch($order->id);

            return $order->fresh();
        });
    }
}
