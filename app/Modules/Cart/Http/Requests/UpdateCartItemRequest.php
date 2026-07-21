<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|numeric|gt:0',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.gt' => 'Quantity must be greater than zero.',
        ];
    }
}
