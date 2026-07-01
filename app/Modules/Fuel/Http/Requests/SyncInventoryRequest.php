<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity_available'  => 'required|numeric|min:0',
            'low_stock_threshold' => 'sometimes|numeric|min:0',
            'notes'               => 'sometimes|string|max:500',
        ];
    }
}
