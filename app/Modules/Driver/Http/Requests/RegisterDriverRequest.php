<?php

declare(strict_types=1);

namespace App\Modules\Driver\Http\Requests;

use App\Http\Requests\ApiRequest;

class RegisterDriverRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
