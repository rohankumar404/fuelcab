<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\DTOs\BaseDTO;

final class SendOtpDTO extends BaseDTO
{
    public function __construct(
        public readonly string $phone
    ) {}

    public static function fromArray(array $data): static
    {
        return new self($data['phone']);
    }

    public function toArray(): array
    {
        return [
            'phone' => $this->phone,
        ];
    }
}
