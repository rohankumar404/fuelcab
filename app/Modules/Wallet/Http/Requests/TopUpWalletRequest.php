<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Http\Requests;

use App\Http\Requests\ApiRequest;

class TopUpWalletRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
