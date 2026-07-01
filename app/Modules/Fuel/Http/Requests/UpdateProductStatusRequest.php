<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:active,disabled,soon',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: active, disabled, soon.',
        ];
    }
}
