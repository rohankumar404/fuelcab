<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Http\Requests\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required_without:phone|nullable|email|max:255',
            'phone' => 'required_without:email|nullable|string|max:20',
            'password' => 'required|string|min:8|max:128',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'An email or phone number is required.',
            'phone.required_without' => 'An email or phone number is required.',
            'password.required' => 'A password is required.',
        ];
    }
}
