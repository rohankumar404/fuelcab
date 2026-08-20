<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Models\User;
use App\Modules\Driver\Services\DriverMatchingService;
use App\Modules\Location\DTOs\CoordinatesDTO;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Notifications\NewOrderAssignedToDriverNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Finds and notifies available nearby drivers of a new order.
 * Uses DriverMatchingService to lookup nearby active drivers.
 */
class NotifyNearbyDrivers implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    public int $tries = 3;

    public function __construct(private readonly DriverMatchingService $driverMatchingService) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load(['deliveryAddress', 'vendor']);
        $address = $order->deliveryAddress;

        if (! $address || ! $address->latitude || ! $address->longitude) {
            Log::warning('[NotifyNearbyDrivers] Delivery address coordinates missing.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        Log::info('[NotifyNearbyDrivers] Broadcasting new order to nearby drivers.', [
            'order_id' => $order->id,
            'vendor_id' => $order->vendor_id,
        ]);

        $coordinates = new CoordinatesDTO(
            lat: (float) $address->latitude,
            lng: (float) $address->longitude,
        );

        $drivers = $this->driverMatchingService->findNearestDrivers($coordinates, radiusInKm: 10.0, limit: 10);

        foreach ($drivers as $match) {
            $driverUser = User::find($match['driver_id']);
            if ($driverUser) {
                // Send notification to nearby driver user
                $driverUser->notify(new NewOrderAssignedToDriverNotification($order));
            }
        }
    }
}
