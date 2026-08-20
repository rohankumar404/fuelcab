<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Requests;

use App\Http\Requests\ApiRequest;

class RefundRequest extends ApiRequest
{
    public function authorize(): bool
    {
        // Require super admin or operations team role for processing refunds
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team']);
    }

    public function rules(): array
    {
        return [
            'payment_id' => ['required', 'uuid', 'exists:payments,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_id.exists' => 'The associated payment transaction was not found.',
            'amount.gt' => 'The refund amount must be greater than zero.',
            'reason.max' => 'Refund reason cannot exceed 255 characters.',
        ];
    }
}
