<?php

declare(strict_types=1);

namespace App\Modules\Driver\Http\Requests;

use App\Enums\UserRole;
use App\Http\Requests\ApiRequest;

class UpdateLocationRequest extends ApiRequest
{
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $role = auth()->user()->role_type;

        return $role === UserRole::Driver ||
               ($role instanceof \BackedEnum && $role->value === 'driver') ||
               $role === 'driver';
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'The longitude must be between -180 and 180 degrees.',
            'speed.min' => 'Speed cannot be negative.',
            'heading.between' => 'Heading must be a direction between 0 and 360 degrees.',
        ];
    }
}
