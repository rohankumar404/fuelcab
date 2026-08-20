<?php

declare(strict_types=1);

namespace App\Modules\Driver\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Driver\Actions\ToggleAvailabilityAction;
use App\Modules\Driver\Models\Driver;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    use ApiResponse;

    /**
     * Get the authenticated driver's profile with active vehicle details.
     *
     * Route: GET /api/v1/drivers/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $driver = Driver::where('user_id', $user->id)
            ->with(['activeVehicle'])
            ->first();

        if (! $driver) {
            return $this->error(
                message: 'Driver profile not found.',
                statusCode: 404
            );
        }

        return $this->success(
            data: [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_type' => $user->role_type,
                'license_number' => $driver->license_number,
                'license_expiry' => $driver->license_expiry?->toDateString(),
                'status' => $driver->status,
                'is_approved' => $driver->is_approved,
                'approved_at' => $driver->approved_at,
                'active_vehicle' => $driver->activeVehicle->first() ? [
                    'id' => $driver->activeVehicle->first()->id,
                    'registration_number' => $driver->activeVehicle->first()->registration_number,
                    'make' => $driver->activeVehicle->first()->make,
                    'model' => $driver->activeVehicle->first()->model,
                    'capacity_liters' => $driver->activeVehicle->first()->capacity_liters,
                    'fuel_type' => $driver->activeVehicle->first()->fuel_type,
                ] : null,
            ],
            message: 'Driver profile retrieved successfully.'
        );
    }

    /**
     * Toggle availability status of a driver.
     *
     * Route: POST /api/v1/drivers/availability
     */
    public function toggleAvailability(Request $request, ToggleAvailabilityAction $action): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:offline,available,on_trip,suspended'],
        ]);

        $driver = $action->execute(
            $request->user()->id,
            $request->input('status')
        );

        return $this->success(
            data: [
                'status' => $driver->status,
            ],
            message: 'Driver availability status updated to '.$driver->status.'.'
        );
    }

    /**
     * Get assigned active orders/trips for the authenticated driver.
     *
     * Route: GET /api/v1/drivers/orders/assigned
     */
    public function assignedOrders(Request $request): JsonResponse
    {
        $orders = Order::where('driver_id', $request->user()->id)
            ->whereIn('status', [
                OrderStatus::Assigned,
                OrderStatus::OutForDelivery,
            ])
            ->with(['customer:id,name,phone', 'deliveryAddress'])
            ->latest()
            ->get();

        // Dynamically auto-generate delivery OTP if not already set to ensure delivery verification flow
        foreach ($orders as $order) {
            if (empty($order->delivery_otp)) {
                $order->update([
                    'delivery_otp' => (string) rand(100000, 999999),
                ]);
            }
        }

        return $this->success(
            data: $orders->map(fn ($o) => [
                'id' => $o->id,
                'status' => $o->status->value,
                'total_amount' => $o->total_amount,
                'scheduled_delivery_at' => $o->scheduled_delivery_at?->toDateTimeString(),
                'delivery_otp' => $o->delivery_otp,
                'customer' => [
                    'name' => $o->customer?->name,
                    'phone' => $o->customer?->phone,
                ],
                'delivery_address' => $o->deliveryAddress?->full_address,
            ]),
            message: 'Assigned orders retrieved successfully.'
        );
    }

    /**
     * Get completed/cancelled trip history of the driver.
     *
     * Route: GET /api/v1/drivers/orders/trips
     */
    public function tripHistory(Request $request): JsonResponse
    {
        $orders = Order::where('driver_id', $request->user()->id)
            ->whereIn('status', [
                OrderStatus::Delivered,
                OrderStatus::Cancelled,
            ])
            ->with(['customer:id,name', 'deliveryAddress'])
            ->latest()
            ->paginate(15);

        return $this->success(
            data: $orders,
            message: 'Driver trip history retrieved successfully.'
        );
    }

    /**
     * Verify delivery OTP provided by the customer.
     *
     * Route: POST /api/v1/drivers/orders/{orderId}/verify-otp
     */
    public function verifyOtp(Request $request, string $orderId): JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $order = Order::where('id', $orderId)
            ->where('driver_id', $request->user()->id)
            ->firstOrFail();

        if ($order->delivery_otp !== $request->input('otp')) {
            return $this->error(
                message: 'Invalid OTP code. Please verify and try again.',
                statusCode: 422
            );
        }

        $order->update([
            'otp_verified_at' => now(),
        ]);

        return $this->success(
            data: [
                'otp_verified' => true,
            ],
            message: 'Delivery OTP code verified successfully.'
        );
    }

    /**
     * Complete order delivery by uploading signature & photo proof.
     *
     * Route: POST /api/v1/drivers/orders/{orderId}/complete
     */
    public function completeOrder(Request $request, string $orderId): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'string'], // path or base64
            'signature' => ['required', 'string'], // path or base64
        ]);

        $order = Order::where('id', $orderId)
            ->where('driver_id', $request->user()->id)
            ->firstOrFail();

        // Enforce OTP verification check if OTP is set
        if (! empty($order->delivery_otp) && empty($order->otp_verified_at)) {
            return $this->error(
                message: 'OTP verification is required before completing this delivery.',
                statusCode: 422
            );
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'delivery_proof_photo' => $request->input('photo'),
                'delivery_proof_signature' => $request->input('signature'),
                'status' => OrderStatus::Delivered,
                'delivered_at' => now(),
            ]);

            // Fire OrderCompleted event to trigger settlement and invoice mail
            event(new OrderCompleted($order));
        });

        // Toggle driver availability back to 'available' after trip completion
        $driver = Driver::where('user_id', $request->user()->id)->first();
        if ($driver && $driver->status === 'on_trip') {
            $driver->update(['status' => 'available']);
        }

        return $this->success(
            message: 'Delivery completed and order marked as delivered.'
        );
    }
}
