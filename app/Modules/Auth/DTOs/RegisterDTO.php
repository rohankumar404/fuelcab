<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\DTOs\BaseDTO;

final class RegisterDTO extends BaseDTO
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
