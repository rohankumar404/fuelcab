<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use App\Http\Requests\ApiRequest;

class UpdateOrderRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
