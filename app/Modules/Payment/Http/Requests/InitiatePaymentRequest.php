<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Requests;

use App\Http\Requests\ApiRequest;

class InitiatePaymentRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
