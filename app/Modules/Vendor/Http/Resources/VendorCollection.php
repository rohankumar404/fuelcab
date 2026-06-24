<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorCollection extends ResourceCollection
{
    public $collects = VendorResource::class;

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
