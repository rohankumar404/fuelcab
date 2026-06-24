<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentCollection extends ResourceCollection
{
    public $collects = PaymentResource::class;

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
