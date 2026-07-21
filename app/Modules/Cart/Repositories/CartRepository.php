<?php

declare(strict_types=1);

namespace App\Modules\Cart\Repositories;

use App\Models\User;
use App\Modules\Cart\Models\Cart;

class CartRepository
{
    private const DEFAULT_RELATIONS = ['items.product', 'items.vendorListing', 'items.vendor'];

    /**
     * Get or create the authenticated user's cart.
     */
    public function findOrCreateForUser(User $user): Cart
    {
        return Cart::with(self::DEFAULT_RELATIONS)
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['created_by' => $user->id],
            );
    }

    /**
     * Get or create a guest cart by token.
     */
    public function findOrCreateForGuest(string $token): Cart
    {
        return Cart::with(self::DEFAULT_RELATIONS)
            ->firstOrCreate(
                ['guest_token' => $token, 'user_id' => null],
                ['guest_token' => $token],
            );
    }

    /**
     * Find a guest cart by token.
     */
    public function findByGuestToken(string $token): ?Cart
    {
        return Cart::with(self::DEFAULT_RELATIONS)
            ->where('guest_token', $token)
            ->whereNull('user_id')
            ->first();
    }

    /**
     * Find a cart by ID with items loaded.
     */
    public function findById(string $cartId): Cart
    {
        return Cart::with(self::DEFAULT_RELATIONS)
            ->findOrFail($cartId);
    }
}
