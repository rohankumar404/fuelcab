<?php

declare(strict_types=1);

namespace App\Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint — no auth required
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'delivery_date' => ['nullable', 'date'],
            'message' => ['nullable', 'string', 'max:2000'],
            'product_name' => ['nullable', 'string', 'max:200'],
            'listing_slug' => ['nullable', 'string', 'max:200'],
            'vendor_name' => ['nullable', 'string', 'max:200'],
        ];
    }
}
