<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Requests;

use App\Http\Requests\ApiRequest;

class FilterUsersRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team']);
    }

    public function rules(): array
    {
        return [
            'role_type' => ['nullable', 'string', 'in:super_admin,operations_team,vendor_admin,vendor_staff,driver,customer'],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_type.in' => 'The selected role type filter is invalid.',
            'status.in' => 'The selected status filter is invalid.',
            'per_page.max' => 'Cannot request more than 100 users per page.',
        ];
    }
}
