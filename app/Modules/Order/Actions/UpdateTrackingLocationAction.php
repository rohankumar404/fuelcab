<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderTracking;
use Illuminate\Support\Facades\DB;

class UpdateTrackingLocationAction
{
    public function execute(string $orderId, float $latitude, float $longitude): OrderTracking
    {
        return DB::transaction(function () use ($orderId, $latitude, $longitude) {
            $order = Order::findOrFail($orderId);

            // Resolve the drivers.id from the user_id (driver_id stored on the order)
            $driverId = null;
            if ($order->driver_id) {
                $driverId = DB::table('drivers')
                    ->where('user_id', $order->driver_id)
                    ->value('id');
            }

            return OrderTracking::create([
                'order_id'    => $order->id,
                'driver_id'   => $driverId,
                'latitude'    => $latitude,
                'longitude'   => $longitude,
                'status'      => $order->status->value,
                'recorded_at' => now(),
            ]);
        });
    }
}
