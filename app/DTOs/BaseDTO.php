<?php

declare(strict_types=1);

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * Create DTO from an associative array.
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Convert DTO to an associative array.
     */
    abstract public function toArray(): array;
}
