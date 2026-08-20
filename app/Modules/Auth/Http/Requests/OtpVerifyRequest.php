<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Http\Requests\ApiRequest;

class OtpVerifyRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|digits:6',
        ];
    }

    public function messages(): array
    {
        return [
            'otp.digits' => 'The OTP must be exactly 6 digits.',
        ];
    }
}
