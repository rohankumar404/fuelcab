<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use App\Http\Requests\ApiRequest;

class FilterOrderRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status'         => ['nullable', 'string', 'in:placed,accepted,preparing,out_for_delivery,delivered,completed,cancelled'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,failed,refunded'],
            'from_date'      => ['nullable', 'date'],
            'to_date'        => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'          => 'The selected order status filter is invalid.',
            'payment_status.in'  => 'The selected payment status filter is invalid.',
            'to_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
