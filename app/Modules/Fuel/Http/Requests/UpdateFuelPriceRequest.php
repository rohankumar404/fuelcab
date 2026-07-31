<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Requests;

use App\Http\Requests\ApiRequest;

class UpdateFuelPriceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team', 'vendor_admin']);
    }

    public function rules(): array
    {
        return [
            'base_price' => ['required', 'numeric', 'gt:0'],
            'tax_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'currency'   => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'base_price.gt' => 'The base price must be greater than zero.',
            'tax_rate.max'  => 'The tax rate cannot exceed 100%.',
        ];
    }
}
