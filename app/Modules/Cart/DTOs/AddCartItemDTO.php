<?php

declare(strict_types=1);

namespace App\Modules\Cart\DTOs;

final class AddCartItemDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly float  $quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity:  (float) $data['quantity'],
        );
    }
}
