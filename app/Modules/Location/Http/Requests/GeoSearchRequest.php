<?php

declare(strict_types=1);

namespace App\Modules\Location\Http\Requests;

use App\Http\Requests\ApiRequest;

class GeoSearchRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
