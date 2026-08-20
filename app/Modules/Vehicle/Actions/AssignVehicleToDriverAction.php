<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Actions;

use App\Modules\Driver\Models\Driver;
use App\Modules\Vehicle\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignVehicleToDriverAction
{
    /**
     * Assign a vehicle to a driver.
     *
     * Marks previous assignments for this driver as inactive,
     * and sets the new assignment as active.
     */
    public function execute(string $driverId, string $vehicleId): void
    {
        DB::transaction(function () use ($driverId, $vehicleId): void {
            // Verify driver and vehicle exist
            $driver = Driver::findOrFail($driverId);
            $vehicle = Vehicle::findOrFail($vehicleId);

            // Mark any current active assignments for this driver as inactive
            DB::table('driver_vehicle')
                ->where('driver_id', $driverId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'unassigned_at' => now(),
                    'updated_at' => now(),
                ]);

            // Create/update assignment to active
            DB::table('driver_vehicle')->updateOrInsert(
                [
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                ],
                [
                    'id' => DB::raw('gen_random_uuid()'),
                    'is_active' => true,
                    'assigned_at' => now(),
                    'unassigned_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Log::info('[AssignVehicleToDriverAction] Vehicle assigned to driver.', [
                'driver_id' => $driverId,
                'vehicle_id' => $vehicleId,
            ]);
        });
    }
}
