<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Cart\DTOs\UpdateCartItemDTO;
use App\Modules\Cart\Http\Requests\AddCartItemRequest;
use App\Modules\Cart\Http\Requests\UpdateCartItemRequest;
use App\Modules\Cart\Http\Resources\CartResource;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    private function getGuestToken(Request $request): ?string
    {
        return $request->header('X-Guest-Token')
            ?? $request->input('guest_token')
            ?? $request->cookie('guest_token');
    }

    /**
     * GET /api/v1/cart
     * View the authenticated or guest user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user       = $request->user();
            $guestToken = $this->getGuestToken($request);

            $cart = $this->cartService->resolveCart($user, $guestToken);

            return response()->json([
                'success' => true,
                'data'    => new CartResource($cart->load(['items.product', 'items.vendorListing', 'items.vendor'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * POST /api/v1/cart/items
     * Add a Direct Product or Marketplace Listing to the cart.
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        try {
            $user       = $request->user();
            $guestToken = $this->getGuestToken($request);

            $item = $this->cartService->addItemForContext(
                $user,
                $guestToken,
                AddCartItemDTO::fromArray($request->validated()),
            );

            $cart = $this->cartService->resolveCart($user, $guestToken);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'data'    => new CartResource($cart->load(['items.product', 'items.vendorListing', 'items.vendor'])),
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * PATCH /api/v1/cart/items/{itemId}
     * Update the quantity of an existing cart item.
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        try {
            $user       = $request->user();
            $guestToken = $this->getGuestToken($request);

            $item = CartItem::findOrFail($itemId);
            $cart = $this->cartService->resolveCart($user, $guestToken);

            if ($item->cart_id !== $cart->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $this->cartService->updateItem(
                $item,
                UpdateCartItemDTO::fromArray($request->validated()),
            );

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated.',
                'data'    => new CartResource($cart->load(['items.product', 'items.vendorListing', 'items.vendor'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * DELETE /api/v1/cart/items/{itemId}
     * Remove a single item from the cart.
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $user       = $request->user();
        $guestToken = $this->getGuestToken($request);

        $item = CartItem::findOrFail($itemId);
        $cart = $this->cartService->resolveCart($user, $guestToken);

        if ($item->cart_id !== $cart->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $this->cartService->removeItem($cart, $item);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data'    => new CartResource($cart->fresh(['items.product', 'items.vendorListing', 'items.vendor'])),
        ]);
    }

    /**
     * DELETE /api/v1/cart
     * Clear the entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $user       = $request->user();
        $guestToken = $this->getGuestToken($request);

        $cart = $this->cartService->resolveCart($user, $guestToken);
        $this->cartService->clear($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }

    /**
     * POST /api/v1/cart/merge
     * Merge a guest cart into the authenticated user's cart (called on login).
     */
    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'guest_token' => 'required|string|max:100',
        ]);

        $cart = $this->cartService->mergeGuestCart(
            $request->input('guest_token'),
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Guest cart merged successfully.',
            'data'    => new CartResource($cart->load(['items.product', 'items.vendorListing', 'items.vendor'])),
        ]);
    }

    /**
     * POST /api/v1/cart/coupon
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        try {
            $user       = $request->user();
            $guestToken = $this->getGuestToken($request);

            $cart = $this->cartService->applyCoupon($user, $guestToken, $request->input('code'));

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'data'    => new CartResource($cart->load(['items.product', 'items.vendorListing', 'items.vendor'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * DELETE /api/v1/cart/coupon
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            $user       = $request->user();
            $guestToken = $this->getGuestToken($request);

            $cart = $this->cartService->removeCoupon($user, $guestToken);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed.',
                'data'    => new CartResource($cart->load(['items.product', 'items.vendorListing', 'items.vendor'])),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
