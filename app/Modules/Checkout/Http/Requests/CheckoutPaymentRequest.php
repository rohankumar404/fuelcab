<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkout_id'    => ['required', 'uuid', 'exists:checkouts,id'],
            'payment_method' => ['required', 'string', 'in:stripe,razorpay,wallet'],
        ];
    }
}
