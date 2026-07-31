<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Requests;

use App\Http\Requests\ApiRequest;

class CreateVendorRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Used during vendor onboarding registration
    }

    public function rules(): array
    {
        return [
            'brand_name'     => ['required', 'string', 'min:2', 'max:150'],
            'company_name'   => ['required', 'string', 'min:2', 'max:150'],
            'tax_number'     => ['required', 'string', 'max:50', 'unique:vendors,tax_number'],
            'contact_person' => ['required', 'string', 'min:2', 'max:100'],
            'mobile'         => ['required', 'string', 'max:20', 'unique:vendors,mobile'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:vendors,email'],
            'address_line1'  => ['required', 'string', 'max:255'],
            'address_line2'  => ['nullable', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'state'          => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'tax_number.unique' => 'A vendor with this Tax/GST number is already registered.',
            'mobile.unique'     => 'This mobile number is already linked to another vendor account.',
            'email.unique'      => 'This email address is already linked to another vendor account.',
        ];
    }
}
