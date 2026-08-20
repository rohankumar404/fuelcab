<?php

declare(strict_types=1);

namespace App\Modules\Driver\Http\Requests;

use App\Http\Requests\ApiRequest;

class RegisterDriverRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Used during driver onboarding registration
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'license_number' => ['required', 'string', 'max:50', 'unique:drivers,license_number'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'vehicle_plate_number' => ['required', 'string', 'max:50', 'unique:drivers,vehicle_plate_number'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email address already exists.',
            'phone.unique' => 'A user with this phone number already exists.',
            'license_number.unique' => 'This driving license number is already registered.',
            'vehicle_plate_number.unique' => 'This vehicle plate number is already registered.',
        ];
    }
}
