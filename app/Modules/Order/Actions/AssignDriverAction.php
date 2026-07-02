<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderAssigned;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignDriverAction
{
    public function execute(string $orderId, string $driverId): Order
    {
        return DB::transaction(function () use ($orderId, $driverId) {
            $order = Order::findOrFail($orderId);
            $driver = User::findOrFail($driverId);

            // Ensure the user has the 'driver' role
            if (! $driver->hasRole('driver')) {
                throw new \InvalidArgumentException("User is not a driver.");
            }

            if (! $order->status->canTransitionTo(OrderStatus::Assigned)) {
                throw new \DomainException("Cannot transition order status from '{$order->status->value}' to 'assigned'.");
            }

            $order->update([
                'driver_id' => $driver->id,
                'status'    => OrderStatus::Assigned,
            ]);

            event(new OrderAssigned($order));

            return $order->fresh(['driver']);
        });
    }
}
