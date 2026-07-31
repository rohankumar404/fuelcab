<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Requests;

use App\Http\Requests\ApiRequest;

class InitiatePaymentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'       => ['required', 'uuid', 'exists:orders,id'],
            'payment_method' => ['required', 'string', 'in:stripe,razorpay,wallet'],
            'amount'         => ['required', 'numeric', 'gt:0'],
            'currency'       => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.exists'    => 'The selected order is invalid or does not exist.',
            'payment_method.in'  => 'Available payment methods are stripe, razorpay, or wallet.',
            'amount.gt'          => 'The payment amount must be greater than zero.',
        ];
    }
}
