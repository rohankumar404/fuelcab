<?php

declare(strict_types=1);

namespace App\Modules\Driver\DTOs;

use App\DTOs\BaseDTO;

final class DriverLocationDTO extends BaseDTO
{
    public function __construct(
        public readonly string $driverId,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $heading = null,
        public readonly ?float $speedKmh = null,
        public readonly ?string $orderId = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            driverId: (string) ($data['driver_id'] ?? ''),
            latitude: (float) ($data['latitude'] ?? 0.0),
            longitude: (float) ($data['longitude'] ?? 0.0),
            heading: isset($data['heading']) ? (float) $data['heading'] : null,
            speedKmh: isset($data['speed']) ? (float) $data['speed'] : null,
            orderId: $data['order_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'driver_id' => $this->driverId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'heading' => $this->heading,
            'speed_kmh' => $this->speedKmh,
            'order_id' => $this->orderId,
        ];
    }
}
