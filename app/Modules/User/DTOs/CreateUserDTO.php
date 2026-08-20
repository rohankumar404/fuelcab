<?php

declare(strict_types=1);

namespace App\Modules\User\DTOs;

use App\DTOs\BaseDTO;

final class CreateUserDTO extends BaseDTO
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
