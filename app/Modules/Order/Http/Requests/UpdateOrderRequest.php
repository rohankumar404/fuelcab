<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use App\Http\Requests\ApiRequest;

class UpdateOrderRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team', 'vendor_admin', 'driver']);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:placed,accepted,preparing,out_for_delivery,delivered,completed,cancelled'],
            'payment_status' => ['nullable', 'string', 'in:pending,paid,failed,refunded'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'dispatch_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'The specified status is invalid.',
            'payment_status.in' => 'The specified payment status is invalid.',
            'delivery_notes.max' => 'Delivery notes cannot exceed 1000 characters.',
        ];
    }
}
