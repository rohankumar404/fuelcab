<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Http\Requests\UpdateTrackingLocationRequest;
use App\Modules\Order\Models\OrderTracking;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * POST /api/v1/orders/{id}/tracking
     * Log driver coordinates in real-time.
     */
    public function store(UpdateTrackingLocationRequest $request, string $id): JsonResponse
    {
        try {
            $tracking = $this->orderService->recordLocation(
                orderId:   $id,
                latitude:  (float) $request->validated('latitude'),
                longitude: (float) $request->validated('longitude')
            );

            return response()->json([
                'success' => true,
                'message' => 'Tracking coordinate recorded.',
                'data'    => [
                    'id'          => $tracking->id,
                    'order_id'    => $tracking->order_id,
                    'latitude'    => $tracking->latitude,
                    'longitude'   => $tracking->longitude,
                    'status'      => $tracking->status,
                    'recorded_at' => $tracking->recorded_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/orders/{id}/tracking
     * Get real-time tracking coordinate log trail for a specific order.
     */
    public function track(string $id): JsonResponse
    {
        $trail = OrderTracking::where('order_id', $id)
            ->orderBy('recorded_at', 'desc')
            ->get();

        $latest = $trail->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'order_id'                  => $id,
                'status'                    => $latest ? $latest->status : 'pending',
                'estimated_arrival_minutes' => $latest ? 15 : null, // Mocked for GPS projection
                'latest_location'           => $latest ? [
                    'latitude'  => $latest->latitude,
                    'longitude' => $latest->longitude,
                ] : null,
                'coordinate_trail'          => $trail->map(fn ($item) => [
                    'latitude'    => $item->latitude,
                    'longitude'   => $item->longitude,
                    'recorded_at' => $item->recorded_at->toIso8601String(),
                ]),
            ],
        ]);
    }
}
