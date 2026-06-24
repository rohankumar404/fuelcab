<?php

declare(strict_types=1);

namespace App\Modules\Driver\Services;

use App\Modules\Location\DTOs\CoordinatesDTO;

class DriverMatchingService
{
    /**
     * Find nearest drivers for a given coordinates.
     *
     * @param CoordinatesDTO $coordinates
     * @param float $radiusInKm
     * @param int $limit
     * @return array
     */
    public function findNearestDrivers(CoordinatesDTO $coordinates, float $radiusInKm = 5.0, int $limit = 10): array
    {
        // TODO: Implement GIS/PostGIS query using DriverLocation model to find active and nearest drivers.
        return [];
    }
}
