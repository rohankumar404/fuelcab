<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Modules\Fuel\Models\Product;
use App\Modules\Order\Actions\CreateOrderAction;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Http\Requests\AssignDriverRequest;
use App\Modules\Order\Http\Requests\UpdateOrderStatusRequest;
use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusLog;
use App\Modules\Order\Services\OrderService;
use App\Modules\Vendor\Models\Vendor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * POST /api/v1/orders
     * Create a new fuel order directly from the storefront order flow.
     * Accepts a simple payload and orchestrates address, product, and order creation.
     */
    public function store(Request $request, CreateOrderAction $action): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'fuel_type'       => 'required|string',          // diesel, petrol, cng …
            'quantity'        => 'required|numeric|min:1',
            'payment_method'  => 'required|string',
            'total_amount'    => 'required|numeric|min:0',
            'delivery_date'   => 'nullable|string',
            'delivery_slot_id'=> 'nullable|string',
            'notes'           => 'nullable|string|max:1000',
            // Address fields (flat or nested)
            'address'         => 'nullable|array',
        ]);

        // ── 1. Resolve delivery address ──────────────────────────────────────
        $addrData = $validated['address'] ?? [];
        $line1 = $addrData['line1'] ?? ($addrData['address_line_1'] ?? 'Main Address');
        $city = $addrData['city'] ?? 'India';
        $postal_code = $addrData['pincode'] ?? ($addrData['postal_code'] ?? '000000');

        $address = Address::where('user_id', $user->id)
            ->where('address_line_1', $line1)
            ->where('city', $city)
            ->where('postal_code', $postal_code)
            ->first();

        if (! $address) {
            $address = Address::create([
                'user_id'          => $user->id,
                'addressable_type' => \App\Models\User::class,
                'label'            => 'Delivery',
                'address_line_1'   => $line1,
                'address_line_2'   => $addrData['line2'] ?? ($addrData['address_line_2'] ?? null),
                'city'             => $city,
                'state'            => $addrData['state'] ?? 'India',
                'postal_code'      => $postal_code,
                'country'          => 'India',
                'latitude'         => 0.000000,
                'longitude'        => 0.000000,
                'is_default'       => true,
            ]);
        }

        // ── 2. Resolve fuel product ──────────────────────────────────────────
        $slugMap = [
            'diesel'  => 'diesel-hsd',
            'petrol'  => 'petrol',
            'cng'     => 'cng',
            'lpg'     => 'lpg',
        ];
        $slug    = $slugMap[strtolower((string) $validated['fuel_type'])] ?? ('diesel-hsd');
        $product = Product::where('slug', $slug)->first() ?? Product::first();

        if (! $product) {
            return $this->error('No fuel products configured in the system.', null, 422);
        }

        $qty      = (float) $validated['quantity'];
        $price    = (float) $product->price_per_unit;
        $subtotal = round($qty * $price, 2);
        $total    = (float) $validated['total_amount'];
        $tax      = round($total - $subtotal - 500, 2); // approx: total - subtotal - delivery

        // ── 3. Resolve vendor (FuelCab Direct) ──────────────────────────────
        $vendor = Vendor::first();

        // ── 4. Create order via action (fires confirmation email) ────────────
        try {
            $order = $action->execute([
                'customer_id'          => $user->id,
                'vendor_id'            => $vendor?->id,
                'delivery_address_id'  => $address->id,
                'payment_method'       => $validated['payment_method'],
                'channel'              => 'direct',
                'delivery_fee'         => 500.00,
                'tax_amount'           => max($tax, 0),
                'notes'                => $validated['notes'] ?? null,
                'scheduled_delivery_at'=> $validated['delivery_date'] ?? null,
                'items' => [[
                    'product_id'              => $product->id,
                    'vendor_id'               => $vendor?->id,
                    'quantity'                => $qty,
                    'price_per_unit'          => $price,
                    'unit_of_measure'         => 'liter',
                    'unit_snapshot'           => 'liter',
                    'sales_channel'           => 'direct',
                    'product_name_snapshot'   => $product->name,
                    'product_sku_snapshot'    => $product->sku ?? $product->slug,
                ]],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data'    => new OrderResource($order),
            ], 201);
        } catch (\Throwable $e) {
            return $this->error('Failed to place order: '.$e->getMessage(), null, 422);
        }
    }

    /**
     * GET /api/v1/orders
     * List orders scoped to the authenticated user's role.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $orders = Order::query()
            ->when($user->hasRole('customer'), fn ($q) => $q->where('customer_id', $user->id))
            ->when($user->hasRole('driver'), fn ($q) => $q->where('driver_id', $user->id))
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
            'data' => new OrderResource($order),
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
                'data' => new OrderResource($order->load(['customer', 'vendor'])),
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
                'data' => new OrderResource($order->load(['customer', 'vendor', 'driver'])),
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
                'data' => new OrderResource($order->load(['customer', 'vendor', 'driver'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/orders/{id}/confirm-delivery
     * Driver confirms delivery by submitting the OTP provided to the customer.
     * Also accepts optional photo proof path.
     */
    public function confirmDelivery(Request $request, string $id): JsonResponse
    {
        $order = Order::where('driver_id', $request->user()->id)
            ->where('status', OrderStatus::OutForDelivery->value)
            ->findOrFail($id);

        $validated = $request->validate([
            'otp' => 'required|string|max:10',
            'delivery_proof_photo' => 'nullable|string|max:500',
            'delivery_proof_signature' => 'nullable|string',
        ]);

        if ($order->delivery_otp && $order->delivery_otp !== $validated['otp']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid delivery OTP. Please ask the customer for the correct code.',
            ], 422);
        }

        $order->update([
            'status' => OrderStatus::Delivered,
            'delivered_at' => now(),
            'otp_verified_at' => now(),
            'delivery_proof_photo' => $validated['delivery_proof_photo'] ?? null,
            'delivery_proof_signature' => $validated['delivery_proof_signature'] ?? null,
        ]);

        // Log status change
        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => OrderStatus::OutForDelivery->value,
            'to_status' => OrderStatus::Delivered->value,
            'changed_by' => $request->user()->id,
        ]);

        // Fire delivery completed event for notifications / settlement
        event(new OrderCompleted($order->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed. Order marked as delivered.',
            'data' => new OrderResource($order->fresh()->load(['customer', 'vendor', 'driver', 'items'])),
        ]);
    }
}
