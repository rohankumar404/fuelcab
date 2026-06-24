<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    /**
     * All API requests are authorized by default.
     * Override in specific requests to check policies/abilities.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Override failed validation to return the standard API error envelope
     * instead of Laravel's default redirect/JSON behavior.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data'    => null,
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
