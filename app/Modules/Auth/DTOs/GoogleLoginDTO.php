<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\DTOs\BaseDTO;

final class GoogleLoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $googleId,
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $avatar = null,
        public readonly ?string $token = null,
        public readonly ?string $roleType = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['google_id'],
            $data['email'],
            $data['name'],
            $data['avatar'] ?? null,
            $data['token'] ?? null,
            $data['role_type'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'google_id' => $this->googleId,
            'email' => $this->email,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'token' => $this->token,
            'role_type' => $this->roleType,
        ];
    }
}
