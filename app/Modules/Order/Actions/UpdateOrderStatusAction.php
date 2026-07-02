<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderDispatched;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Events\OrderCancelled;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusAction
{
    public function execute(string $orderId, OrderStatus $targetStatus, ?string $reason = null, ?string $cancelledBy = null): Order
    {
        return DB::transaction(function () use ($orderId, $targetStatus, $reason, $cancelledBy) {
            $order = Order::findOrFail($orderId);

            if (! $order->status->canTransitionTo($targetStatus)) {
                throw new \DomainException("Cannot transition order status from '{$order->status->value}' to '{$targetStatus->value}'.");
            }

            $updateData = ['status' => $targetStatus];
            if ($targetStatus === OrderStatus::Delivered) {
                $updateData['delivered_at'] = now();
            }

            $oldStatus = $order->status;
            $order->update($updateData);

            // Dispatch matching lifecycle events
            match ($targetStatus) {
                OrderStatus::OutForDelivery => event(new OrderDispatched($order)),
                OrderStatus::Delivered => event(new OrderCompleted($order)),
                OrderStatus::Cancelled => event(new OrderCancelled($order, $oldStatus, $reason, $cancelledBy)),
                default => null,
            };

            return $order->fresh();
        });
    }
}
