<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Checkout\Http\Requests\CheckoutInitializeRequest;
use App\Modules\Checkout\Http\Requests\CheckoutAddressRequest;
use App\Modules\Checkout\Http\Requests\CheckoutScheduleRequest;
use App\Modules\Checkout\Http\Requests\CheckoutPaymentRequest;
use App\Modules\Checkout\Http\Resources\CheckoutResource;
use App\Modules\Order\Http\Resources\OrderResource;
use App\Modules\Checkout\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * POST /api/v1/checkout/initialize
     */
    public function initialize(CheckoutInitializeRequest $request): JsonResponse
    {
        try {
            $checkout = $this->checkoutService->initialize(
                (string) $request->user()->id,
                $request->validated('cart_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Checkout initialized successfully.',
                'data'    => new CheckoutResource($checkout),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/checkout/address
     */
    public function selectAddress(CheckoutAddressRequest $request): JsonResponse
    {
        try {
            $checkout = $this->checkoutService->selectAddress(
                (string) $request->user()->id,
                $request->validated('checkout_id'),
                $request->validated('address_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Delivery address selected and coverage verified.',
                'data'    => new CheckoutResource($checkout),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/checkout/schedule
     */
    public function selectSchedule(CheckoutScheduleRequest $request): JsonResponse
    {
        try {
            $checkout = $this->checkoutService->selectSchedule(
                (string) $request->user()->id,
                $request->validated('checkout_id'),
                $request->validated('scheduled_delivery_at')
            );

            return response()->json([
                'success' => true,
                'message' => 'Delivery slot scheduled.',
                'data'    => new CheckoutResource($checkout),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/v1/checkout/{checkoutId}/summary
     */
    public function summary(Request $request, string $checkoutId): JsonResponse
    {
        try {
            $checkout = $this->checkoutService->getSummary(
                (string) $request->user()->id,
                $checkoutId
            );

            return response()->json([
                'success' => true,
                'data'    => new CheckoutResource($checkout),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/checkout/pay
     */
    public function pay(CheckoutPaymentRequest $request): JsonResponse
    {
        try {
            $result = $this->checkoutService->pay(
                (string) $request->user()->id,
                $request->validated('checkout_id'),
                $request->validated('payment_method')
            );

            $result->orders->each(fn ($order) => $order->load(['customer', 'vendor', 'items']));

            return response()->json([
                'success' => true,
                'message' => 'Payment processed and order placed successfully.',
                'data'    => [
                    'payment_id' => $result->payment->id,
                    'orders'     => OrderResource::collection($result->orders),
                    'id'         => $result->primaryOrder()->id,
                    'status'     => $result->primaryOrder()->status->value,
                ],
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
