<?php

declare(strict_types=1);

namespace App\Modules\User\DTOs;

use App\DTOs\BaseDTO;

final class UpdateProfileDTO extends BaseDTO
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
