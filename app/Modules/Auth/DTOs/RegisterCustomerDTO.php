<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\DTOs\BaseDTO;

final class RegisterCustomerDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password']
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password,
        ];
    }
}
