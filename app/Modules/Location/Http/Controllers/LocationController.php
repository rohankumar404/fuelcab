<?php

declare(strict_types=1);

namespace App\Modules\Location\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Modules\Driver\Actions\UpdateLocationAction;
use App\Modules\Driver\DTOs\DriverLocationDTO;
use App\Modules\Driver\Http\Requests\UpdateLocationRequest;
use App\Modules\Driver\Models\DriverLocation;
use App\Modules\Location\Actions\GeocodeAddressAction;
use App\Modules\Location\Services\GoogleMapsService;
use App\Modules\Order\Actions\UpdateTrackingLocationAction;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderTracking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    use ApiResponse;

    /**
     * Address Search / Autocomplete.
     *
     * Route: POST /api/v1/locations/autocomplete
     */
    public function autocomplete(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'session_token' => ['nullable', 'string'],
        ]);

        $results = $maps->autocomplete(
            $request->input('q'),
            $request->input('session_token')
        );

        return $this->success(
            data: $results,
            message: 'Address suggestions retrieved successfully.'
        );
    }

    /**
     * Address Geocoding (Address -> Coords) & Coordinate Storage.
     *
     * Route: POST /api/v1/locations/geocode
     */
    public function geocode(Request $request, GeocodeAddressAction $action): JsonResponse
    {
        $request->validate([
            'address' => ['required', 'string'],
            'address_id' => ['nullable', 'uuid', 'exists:addresses,id'],
        ]);

        $addressModel = null;
        if ($request->filled('address_id')) {
            $addressModel = Address::find($request->input('address_id'));
        }

        $dto = $action->execute($request->input('address'), $addressModel);

        return $this->success(
            data: $dto->toArray(),
            message: 'Address geocoded successfully.'
        );
    }

    /**
     * Get Estimated Time of Arrival (ETA).
     *
     * Route: GET /api/v1/locations/eta
     */
    public function eta(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $request->validate([
            'origin_lat' => ['required', 'numeric', 'between:-90,90'],
            'origin_lng' => ['required', 'numeric', 'between:-180,180'],
            'destination_lat' => ['required', 'numeric', 'between:-90,90'],
            'destination_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $maps->eta(
            (float) $request->input('origin_lat'),
            (float) $request->input('origin_lng'),
            (float) $request->input('destination_lat'),
            (float) $request->input('destination_lng')
        );

        return $this->success(
            data: $result,
            message: 'ETA calculated successfully.'
        );
    }

    /**
     * Get Distance Matrix.
     *
     * Route: GET /api/v1/locations/distance
     */
    public function distance(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $request->validate([
            'origins' => ['required', 'array'],
            'origins.*' => ['required', 'string'],
            'destinations' => ['required', 'array'],
            'destinations.*' => ['required', 'string'],
        ]);

        $results = $maps->distanceMatrix(
            $request->input('origins'),
            $request->input('destinations')
        );

        return $this->success(
            data: $results,
            message: 'Distance matrix calculated successfully.'
        );
    }

    /**
     * Update driver live location coordinates.
     *
     * Route: POST /api/v1/locations/driver/update
     */
    public function updateDriverLocation(UpdateLocationRequest $request, UpdateLocationAction $action): JsonResponse
    {
        $dto = DriverLocationDTO::fromArray([
            'driver_id' => $request->user()->id,
            'latitude' => (float) $request->input('latitude'),
            'longitude' => (float) $request->input('longitude'),
            'speed' => $request->input('speed'),
            'heading' => $request->input('heading'),
            'order_id' => $request->input('order_id'),
        ]);

        $location = $action->execute($dto);

        // If an active order is being tracked, append to order tracking history
        if ($request->filled('order_id')) {
            app(UpdateTrackingLocationAction::class)->execute(
                $request->input('order_id'),
                $dto->latitude,
                $dto->longitude
            );
        }

        return $this->success(
            data: [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'recorded_at' => $location->recorded_at,
            ],
            message: 'Driver location updated successfully.'
        );
    }

    /**
     * Get live driver coordinate position for a customer.
     *
     * Route: GET /api/v1/locations/driver/{orderId}
     */
    public function getLiveDriverPosition(string $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        if (! $order->driver_id) {
            return $this->error(
                message: 'No driver is currently assigned to this order.',
                statusCode: 404
            );
        }

        // Find driver primary key from user_id
        $driverId = DB::table('drivers')->where('user_id', $order->driver_id)->value('id');

        if (! $driverId) {
            return $this->error(
                message: 'Driver profile details not found.',
                statusCode: 404
            );
        }

        $location = DriverLocation::where('driver_id', $driverId)->first();

        if (! $location) {
            return $this->error(
                message: 'No location data available for the assigned driver.',
                statusCode: 404
            );
        }

        return $this->success(
            data: [
                'driver_id' => $order->driver_id,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'heading' => $location->heading ? (float) $location->heading : null,
                'speed_kmh' => $location->speed_kmh ? (float) $location->speed_kmh : null,
                'recorded_at' => $location->recorded_at,
            ],
            message: 'Live driver location retrieved.'
        );
    }

    /**
     * Get order delivery tracking history breadcrumbs.
     *
     * Route: GET /api/v1/locations/tracking/{orderId}
     */
    public function getDeliveryTrackingHistory(string $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        $tracking = OrderTracking::where('order_id', $order->id)
            ->oldest('recorded_at')
            ->get();

        return $this->success(
            data: $tracking,
            message: 'Delivery tracking path retrieved successfully.'
        );
    }

    /**
     * Route Optimization.
     *
     * Route: POST /api/v1/locations/route/optimize
     */
    public function optimizeRoute(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'waypoints.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'waypoints.*.label' => ['nullable', 'string'],
        ]);

        $optimized = $maps->optimizeRoute($request->input('waypoints'));

        return $this->success(
            data: $optimized,
            message: 'Route optimization processed successfully.'
        );
    }
}
