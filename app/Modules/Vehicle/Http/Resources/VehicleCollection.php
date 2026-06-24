<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleCollection extends ResourceCollection
{
    public $collects = VehicleResource::class;

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
