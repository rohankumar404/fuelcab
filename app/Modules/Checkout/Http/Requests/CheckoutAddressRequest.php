<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkout_id' => ['required', 'uuid', 'exists:checkouts,id'],
            'address_id'  => ['required', 'uuid', 'exists:addresses,id'],
        ];
    }
}
