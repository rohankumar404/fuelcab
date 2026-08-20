<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Requests;

use App\Http\Requests\ApiRequest;

class CreateFuelTypeRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:products,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A fuel type with this name already exists.',
            'category_id.exists' => 'The selected product category is invalid.',
        ];
    }
}
