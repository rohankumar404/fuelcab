<?php

declare(strict_types=1);

namespace App\Modules\Fuel\DTOs;

use App\DTOs\BaseDTO;

final class CreateFuelTypeDTO extends BaseDTO
{
    public static function fromArray(array $data): static
    {
        return new static();
    }

    public function toArray(): array
    {
        return [];
    }
}
