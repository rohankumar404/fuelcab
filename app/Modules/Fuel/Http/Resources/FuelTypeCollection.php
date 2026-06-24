<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FuelTypeCollection extends ResourceCollection
{
    public $collects = FuelTypeResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }

    public function with($request): array
    {
        return [
            'success' => true,
        ];
    }
}
