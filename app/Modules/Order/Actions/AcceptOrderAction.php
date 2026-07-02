<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderAccepted;
use Illuminate\Support\Facades\DB;

class AcceptOrderAction
{
    public function execute(string $orderId): Order
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::findOrFail($orderId);

            if (! $order->status->canTransitionTo(OrderStatus::Accepted)) {
                throw new \DomainException("Cannot transition order status from '{$order->status->value}' to 'accepted'.");
            }

            $order->update([
                'status' => OrderStatus::Accepted,
            ]);

            event(new OrderAccepted($order));

            return $order->fresh();
        });
    }
}
