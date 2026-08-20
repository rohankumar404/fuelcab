<?php

declare(strict_types=1);

namespace App\Modules\Location\Actions;

use App\Models\Address;
use App\Modules\Location\DTOs\CoordinatesDTO;
use App\Modules\Location\Services\GoogleMapsService;

class GeocodeAddressAction
{
    public function __construct(
        private readonly GoogleMapsService $maps
    ) {}

    /**
     * Convert a text address to latitude / longitude coordinates.
     *
     * Optionally stores coordinates back to an Address model if one is provided.
     */
    public function execute(string $address, ?Address $addressModel = null): CoordinatesDTO
    {
        $result = $this->maps->geocode($address);

        $dto = CoordinatesDTO::fromArray($result);

        // Store coordinates on the Address model if provided
        if ($addressModel) {
            $addressModel->update([
                'latitude' => $dto->lat,
                'longitude' => $dto->lng,
            ]);
        }

        return $dto;
    }
}
