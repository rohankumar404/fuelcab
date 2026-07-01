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
            'quantity' => 'required|numeric|min:100',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'Minimum order quantity is 100 litres.',
        ];
    }
}
