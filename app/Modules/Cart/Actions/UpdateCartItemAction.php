<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Modules\Cart\DTOs\UpdateCartItemDTO;
use App\Modules\Cart\Models\CartItem;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    private const MIN_QUANTITY = 100.0;

    public function execute(CartItem $item, UpdateCartItemDTO $dto): CartItem
    {
        return DB::transaction(function () use ($item, $dto) {
            if ($dto->quantity < self::MIN_QUANTITY) {
                throw new \DomainException("Minimum order quantity is " . self::MIN_QUANTITY . " litres.");
            }

            $item->update(['quantity' => $dto->quantity]);

            return $item->fresh();
        });
    }
}
