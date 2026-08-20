<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use App\Http\Requests\ApiRequest;

class UpdateProfileRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'name' => ['nullable', 'string', 'min:2', 'max:100'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,'.$userId.',id'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,'.$userId.',id'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email address already exists.',
            'phone.unique' => 'A user with this phone number already exists.',
            'avatar.max' => 'The profile picture size must not exceed 2MB.',
            'avatar.image' => 'The profile picture must be a valid image file.',
        ];
    }
}
