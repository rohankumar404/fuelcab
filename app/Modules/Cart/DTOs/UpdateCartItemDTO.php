<?php

declare(strict_types=1);

namespace App\Modules\Cart\DTOs;

final class UpdateCartItemDTO
{
    public function __construct(
        public readonly float $quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            quantity: (float) $data['quantity'],
        );
    }
}
