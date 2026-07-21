<?php

declare(strict_types=1);

namespace App\Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'        => 'nullable|uuid|exists:products,id|required_without_all:vendor_listing_id,listing_id',
            'vendor_listing_id' => 'nullable|uuid|exists:vendor_listings,id',
            'listing_id'        => 'nullable|uuid|exists:vendor_listings,id',
            'quantity'          => 'required|numeric|gt:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required_without_all' => 'Either product_id or vendor_listing_id must be provided.',
            'quantity.gt'                      => 'Quantity must be greater than zero.',
        ];
    }
}
