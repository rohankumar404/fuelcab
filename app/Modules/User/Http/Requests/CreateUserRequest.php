<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use App\Http\Requests\ApiRequest;

class CreateUserRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role_type, ['super_admin', 'operations_team']);
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'min:2', 'max:100'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'role_type' => ['required', 'string', 'in:super_admin,operations_team,vendor_admin,vendor_staff,driver,customer'],
            'status'    => ['nullable', 'string', 'in:active,inactive,suspended'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'   => 'A user with this email address already exists.',
            'phone.unique'   => 'A user with this phone number already exists.',
            'password.min'   => 'The password must be at least 8 characters long.',
            'role_type.in'   => 'The specified role type is invalid.',
            'status.in'      => 'The specified user status is invalid.',
        ];
    }
}
