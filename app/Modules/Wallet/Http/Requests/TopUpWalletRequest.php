<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Http\Requests;

use App\Http\Requests\ApiRequest;

class TopUpWalletRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10', 'max:1000000'],
            'payment_method' => ['required', 'string', 'in:razorpay,stripe'],
            'payment_gateway_response' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The minimum wallet top-up amount is ₹10.',
            'amount.max' => 'The maximum wallet top-up amount is ₹1,000,000.',
            'payment_method.in' => 'Available payment gateways are razorpay or stripe.',
        ];
    }
}
