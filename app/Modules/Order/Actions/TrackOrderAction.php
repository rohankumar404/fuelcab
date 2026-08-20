<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderTracking;
use Illuminate\Database\Eloquent\Collection;

class TrackOrderAction
{
    /**
     * Retrieve current tracking state and the latest driver coordinates for an order.
     *
     * @return array{order_id: string, status: string, current_location: array|null, history: Collection}
     */
    public function execute(string $orderId): array
    {
        $order = Order::with(['driver', 'deliveryAddress'])
            ->findOrFail($orderId);

        /** @var OrderTracking|null $latest */
        $latest = OrderTracking::where('order_id', $orderId)
            ->orderByDesc('recorded_at')
            ->first();

        $history = OrderTracking::where('order_id', $orderId)
            ->orderByDesc('recorded_at')
            ->take(20)
            ->get(['id', 'latitude', 'longitude', 'status', 'recorded_at']);

        $currentLocation = $latest ? [
            'latitude' => (float) $latest->latitude,
            'longitude' => (float) $latest->longitude,
            'status' => $latest->status,
            'recorded_at' => $latest->recorded_at,
        ] : null;

        return [
            'order_id' => $orderId,
            'status' => $order->status->value,
            'current_location' => $currentLocation,
            'delivery_address' => $order->deliveryAddress,
            'driver' => $order->driver ? [
                'id' => $order->driver->id,
                'name' => $order->driver->name,
            ] : null,
            'history' => $history,
        ];
    }
}
