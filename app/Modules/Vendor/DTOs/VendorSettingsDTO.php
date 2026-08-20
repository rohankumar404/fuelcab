<?php

declare(strict_types=1);

namespace App\Modules\Vendor\DTOs;

use App\DTOs\BaseDTO;

final class VendorSettingsDTO extends BaseDTO
{
    public static function fromArray(array $data): static
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
