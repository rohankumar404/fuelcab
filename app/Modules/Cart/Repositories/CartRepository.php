<?php

declare(strict_types=1);

namespace App\Modules\Cart\Repositories;

use App\Models\User;
use App\Modules\Cart\Models\Cart;

class CartRepository
{
    /**
     * Get or create the authenticated user's cart (with items + products loaded).
     */
    public function findOrCreateForUser(User $user): Cart
    {
        return Cart::with(['items.product'])
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['created_by' => $user->id],
            );
    }

    /**
     * Find a guest cart by token.
     */
    public function findByGuestToken(string $token): ?Cart
    {
        return Cart::with(['items.product'])
            ->where('guest_token', $token)
            ->whereNull('user_id')
            ->first();
    }

    /**
     * Find a cart by ID with items loaded.
     */
    public function findById(string $cartId): Cart
    {
        return Cart::with(['items.product', 'vendor'])
            ->findOrFail($cartId);
    }
}
