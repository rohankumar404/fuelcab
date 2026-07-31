<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Requests;

use App\Http\Requests\ApiRequest;

class UpdateVendorSettingsRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team', 'vendor_admin']);
    }

    public function rules(): array
    {
        return [
            'min_order_quantity'    => ['nullable', 'numeric', 'min:0'],
            'max_order_quantity'    => ['nullable', 'numeric', 'min:0', 'gte:min_order_quantity'],
            'serviceable_radius_km' => ['nullable', 'numeric', 'min:0'],
            'dispatch_location'     => ['nullable', 'string', 'max:255'],
            'tax_rate'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_inclusive'         => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_order_quantity.gte' => 'The maximum order quantity must be greater than or equal to the minimum order quantity.',
            'tax_rate.max'           => 'The tax rate cannot exceed 100%.',
        ];
    }
}
