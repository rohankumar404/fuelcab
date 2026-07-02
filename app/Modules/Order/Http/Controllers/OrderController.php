<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Http\Requests\AssignDriverRequest;
use App\Modules\Order\Http\Requests\UpdateOrderStatusRequest;
use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Policies\OrderPolicy;
use App\Modules\Order\Services\OrderService;
use App\Modules\Order\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * GET /api/v1/orders
     * List orders scoped to the authenticated user's role.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $orders = Order::query()
            ->when($user->hasRole('customer'), fn ($q) => $q->where('customer_id', $user->id))
            ->when($user->hasRole('driver'),   fn ($q) => $q->where('driver_id', $user->id))
            ->when($user->hasRole('vendor_admin') || $user->hasRole('vendor_staff'),
                fn ($q) => $q->where('vendor_id', $user->vendor_id))
            ->with(['customer', 'vendor', 'driver', 'items'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/v1/orders/{id}
     * Show a single order with full relations.
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with([
            'customer',
            'vendor',
            'driver',
            'items.product',
            'deliveryAddress',
            'statusLogs',
            'tracking',
        ])->findOrFail($id);

        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * PATCH /api/v1/orders/{id}/accept
     * Transition a pending order to accepted.
     */
    public function accept(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $this->authorize('accept', $order);

        try {
            $order = $this->orderService->acceptOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Order accepted successfully.',
                'data'    => new OrderResource($order->load(['customer', 'vendor'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PATCH /api/v1/orders/{id}/assign-driver
     * Assign a driver to the order.
     */
    public function assignDriver(AssignDriverRequest $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $this->authorize('assignDriver', $order);

        try {
            $order = $this->orderService->assignDriver(
                $id,
                $request->validated('driver_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Driver assigned successfully.',
                'data'    => new OrderResource($order->load(['customer', 'vendor', 'driver'])),
            ]);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PATCH /api/v1/orders/{id}/status
     * Transition order status dynamically.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $this->authorize('updateStatus', $order);

        try {
            $status = OrderStatus::from($request->validated('status'));
            $reason = $request->validated('reason');

            $order = $this->orderService->updateStatus(
                orderId: $id,
                status: $status,
                reason: $reason
            );

            return response()->json([
                'success' => true,
                'message' => "Order status updated to '{$status->value}'.",
                'data'    => new OrderResource($order->load(['customer', 'vendor', 'driver'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
