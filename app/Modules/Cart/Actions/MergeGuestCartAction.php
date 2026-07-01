<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Models\User;
use App\Modules\Cart\Events\GuestCartMerged;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Repositories\CartRepository;
use Illuminate\Support\Facades\DB;

class MergeGuestCartAction
{
    public function __construct(
        private readonly CartRepository $repository,
    ) {}

    public function execute(string $guestToken, User $user): Cart
    {
        return DB::transaction(function () use ($guestToken, $user) {
            $guestCart = $this->repository->findByGuestToken($guestToken);

            if (! $guestCart || $guestCart->items->isEmpty()) {
                return $this->repository->findOrCreateForUser($user);
            }

            $userCart = $this->repository->findOrCreateForUser($user);
            $merged   = 0;

            foreach ($guestCart->items as $guestItem) {
                $existing = CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $guestItem->product_id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existing) {
                    $existing->update(['quantity' => $existing->quantity + $guestItem->quantity]);
                } else {
                    CartItem::create([
                        'cart_id'         => $userCart->id,
                        'product_id'      => $guestItem->product_id,
                        'quantity'        => $guestItem->quantity,
                        'price_snapshot'  => $guestItem->price_snapshot,
                        'unit_of_measure' => $guestItem->unit_of_measure,
                    ]);
                }

                $guestItem->delete();
                $merged++;
            }

            // Lock to same vendor
            if (! $userCart->vendor_id && $guestCart->vendor_id) {
                $userCart->update(['vendor_id' => $guestCart->vendor_id]);
            }

            $guestCart->delete();

            event(new GuestCartMerged($userCart, $user, $merged));

            return $userCart->fresh(['items.product']);
        });
    }
}
