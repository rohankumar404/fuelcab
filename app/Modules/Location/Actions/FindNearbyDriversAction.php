<?php

declare(strict_types=1);

namespace App\Modules\Location\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FindNearbyDriversAction
{
    /**
     * Find active drivers within a given radius using the Haversine formula.
     *
     * This is a pure DB-level computation — no Maps API call needed.
     * GoogleMapsService is NOT used here to avoid unnecessary API quota usage.
     *
     * @return Collection<int, object{driver_id: string, latitude: float, longitude: float, distance_km: float, heading: ?float, speed_kmh: ?float, recorded_at: string}>
     */
    public function execute(float $lat, float $lng, float $radiusKm = 10.0, int $limit = 20): Collection
    {
        // Haversine formula in raw SQL — works on SQLite (tests) and PostgreSQL (production)
        $haversine = '(6371 * acos(
            cos(radians(?)) * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?)) * sin(radians(latitude))
        ))';

        $results = DB::table('driver_locations')
            ->select([
                'driver_id',
                'latitude',
                'longitude',
                'heading',
                'speed_kmh',
                'recorded_at',
                DB::raw("{$haversine} AS distance_km"),
            ])
            ->addBinding([$lat, $lng, $lat], 'select')
            ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm])
            ->orderBy('distance_km')
            ->limit($limit)
            ->get();

        return $results;
    }
}
