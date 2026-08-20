<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Actions;

use App\Modules\Vehicle\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateVehicleAction
{
    /**
     * Create a new vehicle record.
     *
     * @param  array{
     *   vendor_id: string,
     *   registration_number: string,
     *   make: string,
     *   model: string,
     *   year: int,
     *   capacity_liters: float,
     *   fuel_type?: string,
     *   status?: string,
     *   created_by?: string|null,
     * } $data
     */
    public function execute(array $data): Vehicle
    {
        return DB::transaction(function () use ($data): Vehicle {
            $vehicle = Vehicle::create([
                'vendor_id' => $data['vendor_id'],
                'registration_number' => strtoupper(trim($data['registration_number'])),
                'make' => trim($data['make']),
                'model' => trim($data['model']),
                'year' => (int) $data['year'],
                'capacity_liters' => (float) $data['capacity_liters'],
                'fuel_type' => $data['fuel_type'] ?? 'diesel',
                'status' => $data['status'] ?? 'active',
                'created_by' => $data['created_by'] ?? null,
            ]);

            Log::info('[CreateVehicleAction] Vehicle created.', [
                'vehicle_id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
            ]);

            return $vehicle;
        });
    }
}
