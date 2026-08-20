<?php

declare(strict_types=1);

namespace App\Modules\Location\Http\Requests;

use App\Http\Requests\ApiRequest;

class GeoSearchRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'The longitude must be between -180 and 180 degrees.',
            'radius.max' => 'Search radius cannot exceed 500 kilometers.',
            'limit.max' => 'Cannot request more than 100 results per page.',
        ];
    }
}
