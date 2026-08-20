<?php

declare(strict_types=1);

namespace App\Modules\Location\DTOs;

use App\DTOs\BaseDTO;

final class CoordinatesDTO extends BaseDTO
{
    public function __construct(
        public readonly float $lat,
        public readonly float $lng,
        public readonly string $formattedAddress = '',
        public readonly string $placeId = ''
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            lat: (float) ($data['lat'] ?? $data['latitude'] ?? 0.0),
            lng: (float) ($data['lng'] ?? $data['longitude'] ?? 0.0),
            formattedAddress: (string) ($data['formatted_address'] ?? ''),
            placeId: (string) ($data['place_id'] ?? '')
        );
    }

    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'formatted_address' => $this->formattedAddress,
            'place_id' => $this->placeId,
        ];
    }
}
