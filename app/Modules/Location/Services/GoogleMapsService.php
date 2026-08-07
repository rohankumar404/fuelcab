<?php

declare(strict_types=1);

namespace App\Modules\Location\Services;

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single authoritative Google Maps API client.
 *
 * All Maps API calls in the application MUST go through this service.
 * No other class may call Google APIs directly.
 */
class GoogleMapsService
{
    private readonly string $apiKey;
    private readonly string $language;
    private readonly string $region;
    private readonly int    $timeout;
    private readonly int    $retries;

    private const BASE_PLACES      = 'https://maps.googleapis.com/maps/api/place';
    private const BASE_GEOCODE     = 'https://maps.googleapis.com/maps/api/geocode/json';
    private const BASE_DISTANCE    = 'https://maps.googleapis.com/maps/api/distancematrix/json';
    private const BASE_DIRECTIONS  = 'https://maps.googleapis.com/maps/api/directions/json';

    public function __construct()
    {
        $this->apiKey   = (string) config('fuelcab.maps.api_key', '');
        $this->language = (string) config('fuelcab.maps.language', 'en');
        $this->region   = (string) config('fuelcab.maps.region', 'IN');
        $this->timeout  = (int)    config('fuelcab.maps.timeout', 10);
        $this->retries  = (int)    config('fuelcab.maps.retry_attempts', 2);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Places — Autocomplete
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Return address autocomplete suggestions.
     *
     * @return array<int, array{description: string, place_id: string}>
     */
    public function autocomplete(string $input, ?string $sessionToken = null): array
    {
        if (empty(trim($input))) {
            return [];
        }

        $params = [
            'input'    => $input,
            'key'      => $this->apiKey,
            'language' => $this->language,
            'region'   => $this->region,
            'types'    => 'geocode',
        ];

        if ($sessionToken) {
            $params['sessiontoken'] = $sessionToken;
        }

        $response = $this->get(self::BASE_PLACES . '/autocomplete/json', $params);

        $predictions = $response['predictions'] ?? [];

        return array_map(fn ($p) => [
            'description' => $p['description'] ?? '',
            'place_id'    => $p['place_id'] ?? '',
            'terms'       => $p['terms'] ?? [],
        ], $predictions);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Geocoding
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Convert a text address into latitude / longitude coordinates.
     *
     * @return array{lat: float, lng: float, formatted_address: string, place_id: string}
     */
    public function geocode(string $address): array
    {
        $response = $this->get(self::BASE_GEOCODE, [
            'address'  => $address,
            'key'      => $this->apiKey,
            'language' => $this->language,
            'region'   => $this->region,
        ]);

        $result = $response['results'][0] ?? null;

        if (! $result) {
            Log::warning('[GoogleMapsService] Geocode returned no results.', ['address' => $address]);
            throw new ApiException('Address could not be geocoded: ' . $address, 422);
        }

        $location = $result['geometry']['location'];

        return [
            'lat'               => (float) $location['lat'],
            'lng'               => (float) $location['lng'],
            'formatted_address' => $result['formatted_address'] ?? $address,
            'place_id'          => $result['place_id'] ?? '',
        ];
    }

    /**
     * Convert lat/lng coordinates into a human-readable address.
     *
     * @return array{lat: float, lng: float, formatted_address: string, place_id: string}
     */
    public function reverseGeocode(float $lat, float $lng): array
    {
        $response = $this->get(self::BASE_GEOCODE, [
            'latlng'   => "{$lat},{$lng}",
            'key'      => $this->apiKey,
            'language' => $this->language,
        ]);

        $result = $response['results'][0] ?? null;

        if (! $result) {
            throw new ApiException("No address found for coordinates ({$lat}, {$lng}).", 422);
        }

        $location = $result['geometry']['location'];

        return [
            'lat'               => (float) $location['lat'],
            'lng'               => (float) $location['lng'],
            'formatted_address' => $result['formatted_address'] ?? '',
            'place_id'          => $result['place_id'] ?? '',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Distance Matrix
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Compute distances and durations between origins and destinations.
     *
     * @param  string[] $origins      e.g. ['lat,lng', '...']
     * @param  string[] $destinations e.g. ['lat,lng', '...']
     * @return array<int, array{distance_km: float, duration_seconds: int, duration_text: string, distance_text: string}>
     */
    public function distanceMatrix(array $origins, array $destinations): array
    {
        $response = $this->get(self::BASE_DISTANCE, [
            'origins'      => implode('|', $origins),
            'destinations' => implode('|', $destinations),
            'key'          => $this->apiKey,
            'language'     => $this->language,
            'units'        => 'metric',
            'mode'         => 'driving',
        ]);

        $rows    = $response['rows'] ?? [];
        $results = [];

        foreach ($rows as $row) {
            foreach ($row['elements'] ?? [] as $element) {
                if (($element['status'] ?? '') !== 'OK') {
                    $results[] = [
                        'distance_km'     => 0.0,
                        'duration_seconds' => 0,
                        'distance_text'   => 'N/A',
                        'duration_text'   => 'N/A',
                        'status'          => $element['status'] ?? 'UNKNOWN',
                    ];
                    continue;
                }

                $results[] = [
                    'distance_km'      => round($element['distance']['value'] / 1000, 2),
                    'duration_seconds' => $element['duration']['value'],
                    'distance_text'    => $element['distance']['text'],
                    'duration_text'    => $element['duration']['text'],
                    'status'           => 'OK',
                ];
            }
        }

        return $results;
    }

    // ──────────────────────────────────────────────────────────────────────
    // ETA
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Get estimated time of arrival in minutes from one point to another.
     *
     * @return array{eta_minutes: float, distance_km: float, distance_text: string, duration_text: string}
     */
    public function eta(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $results = $this->distanceMatrix(
            ["{$fromLat},{$fromLng}"],
            ["{$toLat},{$toLng}"]
        );

        $result = $results[0] ?? null;

        if (! $result || ($result['status'] ?? '') !== 'OK') {
            throw new ApiException('Could not compute ETA between the provided coordinates.', 422);
        }

        return [
            'eta_minutes'   => round($result['duration_seconds'] / 60, 1),
            'distance_km'   => $result['distance_km'],
            'distance_text' => $result['distance_text'],
            'duration_text' => $result['duration_text'],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Route Optimisation
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Optimise a delivery route using the Directions API.
     *
     * @param  array<int, array{lat: float, lng: float, label?: string}> $waypoints
     * @return array{optimized_order: int[], legs: array, overview_polyline: string}
     */
    public function optimizeRoute(array $waypoints): array
    {
        if (count($waypoints) < 2) {
            throw new ApiException('At least 2 waypoints are required for route optimisation.', 422);
        }

        $origin      = $waypoints[0];
        $destination = $waypoints[count($waypoints) - 1];
        $middle      = array_slice($waypoints, 1, -1);

        $waypointStr = empty($middle)
            ? ''
            : 'optimize:true|' . implode('|', array_map(
                fn ($w) => "via:{$w['lat']},{$w['lng']}",
                $middle
            ));

        $params = [
            'origin'      => "{$origin['lat']},{$origin['lng']}",
            'destination' => "{$destination['lat']},{$destination['lng']}",
            'key'         => $this->apiKey,
            'language'    => $this->language,
            'mode'        => 'driving',
        ];

        if ($waypointStr) {
            $params['waypoints'] = $waypointStr;
        }

        $response = $this->get(self::BASE_DIRECTIONS, $params);

        $route = $response['routes'][0] ?? null;

        if (! $route) {
            throw new ApiException('No route found for the given waypoints.', 422);
        }

        $legs = array_map(fn ($leg) => [
            'start_address'    => $leg['start_address'] ?? '',
            'end_address'      => $leg['end_address'] ?? '',
            'distance_km'      => round(($leg['distance']['value'] ?? 0) / 1000, 2),
            'duration_minutes' => round(($leg['duration']['value'] ?? 0) / 60, 1),
            'distance_text'    => $leg['distance']['text'] ?? '',
            'duration_text'    => $leg['duration']['text'] ?? '',
        ], $route['legs'] ?? []);

        return [
            'optimized_order'    => $route['waypoint_order'] ?? [],
            'legs'               => $legs,
            'overview_polyline'  => $route['overview_polyline']['points'] ?? '',
            'total_distance_km'  => round(array_sum(array_column($legs, 'distance_km')), 2),
            'total_duration_min' => round(array_sum(array_column($legs, 'duration_minutes')), 1),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Internal HTTP helper
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Make a GET request to a Google API endpoint.
     *
     * @return array<string, mixed>
     */
    private function get(string $url, array $params): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retries, 150)
                ->get($url, $params);

            $body = $response->json();

            $status = $body['status'] ?? 'UNKNOWN';
            if (! in_array($status, ['OK', 'ZERO_RESULTS'], true)) {
                Log::warning('[GoogleMapsService] API returned non-OK status', [
                    'url'    => $url,
                    'status' => $status,
                    'error'  => $body['error_message'] ?? 'No error message',
                ]);

                if ($status === 'REQUEST_DENIED') {
                    throw new ApiException('Google Maps API request denied. Check your API key.', 403);
                }
            }

            return $body;

        } catch (ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[GoogleMapsService] HTTP request failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            throw new ApiException('Maps service unavailable: ' . $e->getMessage(), 503);
        }
    }
}
