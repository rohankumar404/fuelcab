<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Requests;

use App\Http\Requests\ApiRequest;

class FilterUsersRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            // TODO: Add validation rules.
        ];
    }
}
