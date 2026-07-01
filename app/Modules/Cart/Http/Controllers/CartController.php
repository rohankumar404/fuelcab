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

    /**
     * GET /api/v1/cart
     * View the authenticated user's cart with all items.
     */
    public function index(Request $request): CartResource
    {
        $cart = $this->cartService->getCart($request->user());
        return new CartResource($cart->load(['items.product', 'vendor']));
    }

    /**
     * POST /api/v1/cart/items
     * Add a product to the cart.
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        try {
            $item = $this->cartService->addItem(
                $request->user(),
                AddCartItemDTO::fromArray($request->validated()),
            );

            $cart = $this->cartService->getCart($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'data'    => new CartResource($cart->load(['items.product', 'vendor'])),
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }
    }

    /**
     * PATCH /api/v1/cart/items/{itemId}
     * Update the quantity of an existing cart item.
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        try {
            $item = CartItem::findOrFail($itemId);

            $this->cartService->updateItem(
                $item,
                UpdateCartItemDTO::fromArray($request->validated()),
            );

            $cart = $this->cartService->getCart($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated.',
                'data'    => new CartResource($cart->load(['items.product', 'vendor'])),
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
        $item = CartItem::findOrFail($itemId);
        $cart = $this->cartService->getCart($request->user());

        $this->cartService->removeItem($cart, $item);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data'    => new CartResource($cart->fresh(['items.product', 'vendor'])),
        ]);
    }

    /**
     * DELETE /api/v1/cart
     * Clear the entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());
        $this->cartService->clear($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }

    /**
     * POST /api/v1/cart/merge
     * Merge a guest cart into the authenticated user's cart (mobile login).
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
            'data'    => new CartResource($cart->load(['items.product', 'vendor'])),
        ]);
    }
}
