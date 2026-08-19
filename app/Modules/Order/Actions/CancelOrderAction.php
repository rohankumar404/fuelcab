<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    /**
     * Cancel an order with an optional reason.
     *
     * @throws \DomainException if the transition is not allowed
     */
    public function execute(string $orderId, ?string $reason = null, ?string $cancelledBy = null): Order
    {
        return DB::transaction(function () use ($orderId, $reason, $cancelledBy) {
            $order = Order::findOrFail($orderId);

            if (! $order->status->canTransitionTo(OrderStatus::Cancelled)) {
                throw new \DomainException(
                    "Cannot cancel an order with status '{$order->status->value}'. " .
                    'Only pending, accepted, or assigned orders may be cancelled.'
                );
            }

            $oldStatus = $order->status;

            $order->update([
                'status'       => OrderStatus::Cancelled,
                'cancel_reason' => $reason,
            ]);

            // Log the status transition
            OrderStatusLog::create([
                'order_id'    => $order->id,
                'from_status' => $oldStatus->value,
                'to_status'   => OrderStatus::Cancelled->value,
                'reason'      => $reason,
                'changed_by'  => $cancelledBy,
            ]);

            event(new OrderCancelled($order, $oldStatus, $reason, $cancelledBy));

            return $order->fresh();
        });
    }
}
