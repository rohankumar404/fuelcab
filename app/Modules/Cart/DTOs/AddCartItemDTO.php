<?php

declare(strict_types=1);

namespace App\Modules\Cart\DTOs;

final class AddCartItemDTO
{
    public function __construct(
        public readonly ?string $productId,
        public readonly float   $quantity,
        public readonly ?string $vendorListingId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId:       $data['product_id'] ?? null,
            quantity:        (float) $data['quantity'],
            vendorListingId: $data['vendor_listing_id'] ?? $data['listing_id'] ?? null,
        );
    }
}
