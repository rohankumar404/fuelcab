<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    public function __construct(
        string $message = 'An error occurred',
        int $code = 400,
        protected readonly mixed $errors = null,
    ) {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data'    => null,
            'errors'  => $this->errors,
        ], $this->getCode() ?: 400);
    }
}
