<?php

declare(strict_types=1);

namespace App\Modules\Driver\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DriverCollection extends ResourceCollection
{
    public $collects = DriverResource::class;

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
