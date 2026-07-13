<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'vendor_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'brand_name'          => 'sometimes|string|max:255',
            'legal_name'          => 'sometimes|string|max:255',
            'gst_number'          => 'sometimes|string|max:50',
            'pan'                 => 'sometimes|string|max:50',
            'company_type'        => 'sometimes|string|max:100',
            'contact_person'      => 'sometimes|string|max:255',
            'mobile'              => 'sometimes|string|max:50',
            'email'               => 'sometimes|email|max:150',
            'registered_address'  => 'sometimes|string',
            'operational_address' => 'sometimes|string',
            'city'                => 'sometimes|string|max:100',
            'state'               => 'sometimes|string|max:100',
            'pincode'             => 'sometimes|string|max:20',
            'latitude'            => 'sometimes|numeric|between:-90,90',
            'longitude'           => 'sometimes|numeric|between:-180,180',
        ];
    }
}
