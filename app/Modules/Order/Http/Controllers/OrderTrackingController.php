<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Get real-time tracking data for a specific order.
     */
    public function track(string $orderId): JsonResponse
    {
        // TODO: Retrieve current driver coordinates and order status for real-time tracking
        return $this->successResponse([
            'order_id' => $orderId,
            'status' => 'en_route',
            'estimated_arrival_minutes' => 15,
            'current_location' => [
                'latitude' => 12.9716,
                'longitude' => 77.5946,
            ],
        ]);
    }
}
