<?php

declare(strict_types=1);

namespace App\Modules\Location\DTOs;

use App\DTOs\BaseDTO;

final class GeoSearchDTO extends BaseDTO
{
    public function __construct(
        public readonly float $lat,
        public readonly float $lng,
        public readonly float $radiusKm = 10.0,
        public readonly int $limit = 20
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            lat: (float) ($data['latitude'] ?? $data['lat'] ?? 0.0),
            lng: (float) ($data['longitude'] ?? $data['lng'] ?? 0.0),
            radiusKm: (float) ($data['radius'] ?? 10.0),
            limit: (int) ($data['limit'] ?? 20)
        );
    }

    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'radius_km' => $this->radiusKm,
            'limit' => $this->limit,
        ];
    }
}
