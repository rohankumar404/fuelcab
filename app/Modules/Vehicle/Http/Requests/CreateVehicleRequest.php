<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Http\Requests;

use App\Http\Requests\ApiRequest;

class CreateVehicleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team']);
    }

    public function rules(): array
    {
        return [
            'plate_number'    => ['required', 'string', 'max:50', 'unique:vehicles,plate_number'],
            'make'            => ['required', 'string', 'max:100'],
            'model'           => ['required', 'string', 'max:100'],
            'year'            => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'capacity_liters' => ['required', 'numeric', 'gt:0'],
            'status'          => ['nullable', 'string', 'in:available,maintenance,retired,active'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate_number.unique' => 'This vehicle plate number is already registered.',
            'year.max'            => 'The vehicle year cannot be in the future.',
            'capacity_liters.gt'  => 'Vehicle capacity must be greater than zero.',
            'status.in'           => 'The specified status is invalid.',
        ];
    }
}
