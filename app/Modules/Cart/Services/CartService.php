<?php

declare(strict_types=1);

namespace App\Modules\Cart\Services;

use App\Models\User;
use App\Modules\Cart\Actions\AddItemToCartAction;
use App\Modules\Cart\Actions\ClearCartAction;
use App\Modules\Cart\Actions\MergeGuestCartAction;
use App\Modules\Cart\Actions\RemoveCartItemAction;
use App\Modules\Cart\Actions\UpdateCartItemAction;
use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Cart\DTOs\UpdateCartItemDTO;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Repositories\CartRepository;

class CartService
{
    public function __construct(
        private readonly CartRepository       $repository,
        private readonly AddItemToCartAction  $addItem,
        private readonly UpdateCartItemAction $updateItem,
        private readonly RemoveCartItemAction $removeItem,
        private readonly ClearCartAction      $clearCart,
        private readonly MergeGuestCartAction $mergeGuest,
    ) {}

    /**
     * Resolve or create cart for user or guest context.
     */
    public function resolveCart(?User $user, ?string $guestToken = null): Cart
    {
        if ($user) {
            return $this->repository->findOrCreateForUser($user);
        }

        if ($guestToken && trim($guestToken) !== '') {
            return $this->repository->findOrCreateForGuest($guestToken);
        }

        throw new \DomainException('Authentication or guest token is required to access cart.');
    }

    /**
     * Get cart for user.
     */
    public function getCart(User $user): Cart
    {
        return $this->resolveCart($user);
    }

    /**
     * Add item to cart for user or guest context.
     */
    public function addItemForContext(?User $user, ?string $guestToken, AddCartItemDTO $dto): CartItem
    {
        $cart = $this->resolveCart($user, $guestToken);
        return $this->addItem->execute($cart, $dto);
    }

    /**
     * Add a product to user cart.
     */
    public function addItem(User $user, AddCartItemDTO $dto): CartItem
    {
        return $this->addItemForContext($user, null, $dto);
    }

    /**
     * Update quantity of an existing cart item.
     */
    public function updateItem(CartItem $item, UpdateCartItemDTO $dto): CartItem
    {
        return $this->updateItem->execute($item, $dto);
    }

    /**
     * Remove a single item from the cart.
     */
    public function removeItem(Cart $cart, CartItem $item): void
    {
        $this->removeItem->execute($cart, $item);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(Cart $cart): void
    {
        $this->clearCart->execute($cart);
    }

    /**
     * Merge a guest cart into the authenticated user's cart (called on login).
     */
    public function mergeGuestCart(string $guestToken, User $user): Cart
    {
        return $this->mergeGuest->execute($guestToken, $user);
    }
}
