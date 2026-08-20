<?php

declare(strict_types=1);

namespace App\Modules\Driver\Services;

use App\Modules\Location\DTOs\CoordinatesDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverMatchingService
{
    /**
     * Find nearest active drivers for a given coordinate using the Haversine formula.
     *
     * Uses a bounding-box pre-filter (lat/lng index) before exact distance calculation
     * so that the full-table Haversine is only applied to a small candidate set.
     *
     * @param  CoordinatesDTO  $coordinates  Target location.
     * @param  float  $radiusInKm  Search radius in kilometres.
     * @param  int  $limit  Maximum number of results to return.
     * @return array<int, array{driver_id: string, distance_km: float, latitude: float, longitude: float}>
     */
    public function findNearestDrivers(CoordinatesDTO $coordinates, float $radiusInKm = 5.0, int $limit = 10): array
    {
        $lat = $coordinates->lat;
        $lng = $coordinates->lng;

        // Bounding box in degrees (1° lat ≈ 111 km; 1° lng ≈ 111 km * cos(lat))
        $latDelta = $radiusInKm / 111.0;
        $lngDelta = $radiusInKm / (111.0 * cos(deg2rad($lat)));

        $latMin = $lat - $latDelta;
        $latMax = $lat + $latDelta;
        $lngMin = $lng - $lngDelta;
        $lngMax = $lng + $lngDelta;

        try {
            $results = DB::table('driver_locations as dl')
                ->join('drivers as d', 'd.id', '=', 'dl.driver_id')
                ->where('d.status', 'active')
                ->whereBetween('dl.latitude', [$latMin, $latMax])
                ->whereBetween('dl.longitude', [$lngMin, $lngMax])
                ->selectRaw(
                    'dl.driver_id,
                     dl.latitude,
                     dl.longitude,
                     dl.heading,
                     dl.speed_kmh,
                     dl.recorded_at,
                     (6371 * acos(
                         cos(radians(?)) * cos(radians(dl.latitude)) *
                         cos(radians(dl.longitude) - radians(?)) +
                         sin(radians(?)) * sin(radians(dl.latitude))
                     )) AS distance_km',
                    [$lat, $lng, $lat]
                )
                ->having('distance_km', '<=', $radiusInKm)
                ->orderBy('distance_km')
                ->limit($limit)
                ->get();

            Log::info('[DriverMatchingService] Found drivers.', [
                'target_lat' => $lat,
                'target_lng' => $lng,
                'radius_km' => $radiusInKm,
                'count' => $results->count(),
            ]);

            return $results->map(fn ($row) => [
                'driver_id' => $row->driver_id,
                'latitude' => (float) $row->latitude,
                'longitude' => (float) $row->longitude,
                'heading' => $row->heading !== null ? (float) $row->heading : null,
                'speed_kmh' => $row->speed_kmh !== null ? (float) $row->speed_kmh : null,
                'distance_km' => round((float) $row->distance_km, 3),
                'recorded_at' => $row->recorded_at,
            ])->all();

        } catch (\Throwable $e) {
            Log::error('[DriverMatchingService] Failed to query nearest drivers.', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lng' => $lng,
            ]);

            return [];
        }
    }
}
