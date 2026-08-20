<?php

declare(strict_types=1);

namespace App\Modules\Driver\Actions;

use App\Modules\Driver\DTOs\DriverLocationDTO;
use App\Modules\Driver\Events\DriverLocationUpdated;
use App\Modules\Driver\Models\DriverLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateLocationAction
{
    /**
     * Upsert driver GPS coordinates and fire DriverLocationUpdated event.
     *
     * Uses UPDATE-OR-INSERT so driver_locations always has exactly
     * ONE row per driver (keyed by the unique driver_id constraint).
     */
    public function execute(DriverLocationDTO $dto): DriverLocation
    {
        // Resolve driver record id from users.id (order stores user_id as driver_id)
        $driverId = DB::table('drivers')
            ->where('user_id', $dto->driverId)
            ->value('id');

        if (! $driverId) {
            Log::warning('[UpdateLocationAction] Could not resolve driver record for user', [
                'user_id' => $dto->driverId,
            ]);
            // Fall back to using the raw user_id if driver table row not found
            $driverId = $dto->driverId;
        }

        // Upsert — one active location row per driver
        $location = DriverLocation::updateOrCreate(
            ['driver_id' => $driverId],
            [
                'latitude' => $dto->latitude,
                'longitude' => $dto->longitude,
                'heading' => $dto->heading,
                'speed_kmh' => $dto->speedKmh,
                'recorded_at' => now(),
            ]
        );

        event(new DriverLocationUpdated(DriverLocationDTO::fromArray([
            'driver_id' => $driverId,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
            'heading' => $dto->heading,
            'speed' => $dto->speedKmh,
            'order_id' => $dto->orderId,
        ])));

        Log::info('[UpdateLocationAction] Driver location updated', [
            'driver_id' => $driverId,
            'lat' => $dto->latitude,
            'lng' => $dto->longitude,
        ]);

        return $location;
    }
}
