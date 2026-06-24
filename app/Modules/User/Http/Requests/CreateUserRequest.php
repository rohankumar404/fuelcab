<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use App\Http\Requests\ApiRequest;

class CreateUserRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
