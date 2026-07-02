<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use App\Modules\Order\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(OrderStatus::class)],
            'reason' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
