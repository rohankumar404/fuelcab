<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use App\Http\Requests\ApiRequest;

class CreateOrderRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cart_id'        => ['required', 'uuid', 'exists:carts,id'],
            'address_id'     => ['required', 'uuid', 'exists:addresses,id'],
            'payment_method' => ['required', 'string', 'in:stripe,razorpay,wallet'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cart_id.exists'    => 'The specified cart is invalid or does not exist.',
            'address_id.exists' => 'The selected delivery address is invalid or does not exist.',
            'payment_method.in' => 'Available payment methods are stripe, razorpay, or wallet.',
            'notes.max'         => 'Order notes cannot exceed 1000 characters.',
        ];
    }
}
