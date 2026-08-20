<?php

declare(strict_types=1);

namespace App\Modules\Order\Jobs;

use App\Models\User;
use App\Modules\Driver\Services\DriverMatchingService;
use App\Modules\Location\DTOs\CoordinatesDTO;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Notifications\NewOrderAssignedToDriverNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AssignDriverJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(private readonly string $orderId)
    {
        $this->queue = 'high';
    }

    public function handle(DriverMatchingService $driverMatchingService): void
    {
        $order = Order::with('deliveryAddress')->find($this->orderId);

        if (! $order) {
            Log::warning('[AssignDriverJob] Order not found.', ['order_id' => $this->orderId]);

            return;
        }

        if ($order->driver_id) {
            Log::info('[AssignDriverJob] Driver already assigned.', ['order_id' => $this->orderId]);

            return;
        }

        $address = $order->deliveryAddress;

        if (! $address || ! $address->latitude || ! $address->longitude) {
            Log::warning('[AssignDriverJob] Delivery address has no coordinates.', [
                'order_id' => $this->orderId,
                'address_id' => $order->delivery_address_id,
            ]);

            return;
        }

        $coordinates = new CoordinatesDTO(
            lat: (float) $address->latitude,
            lng: (float) $address->longitude,
        );

        $nearestDrivers = $driverMatchingService->findNearestDrivers($coordinates, radiusInKm: 10.0, limit: 5);

        if (empty($nearestDrivers)) {
            Log::warning('[AssignDriverJob] No available drivers found in radius.', [
                'order_id' => $this->orderId,
                'lat' => $coordinates->lat,
                'lng' => $coordinates->lng,
            ]);

            return;
        }

        // Assign the closest available driver
        $closestDriver = $nearestDrivers[0];
        $driverId = $closestDriver['driver_id'];

        $order->update(['driver_id' => $driverId]);

        // Notify the driver
        $driver = User::find($driverId);
        if ($driver) {
            $driver->notify(new NewOrderAssignedToDriverNotification($order));
        }

        Log::info('[AssignDriverJob] Driver assigned to order.', [
            'order_id' => $this->orderId,
            'driver_id' => $driverId,
            'distance_km' => $closestDriver['distance_km'],
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[AssignDriverJob] Failed to assign driver.', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
