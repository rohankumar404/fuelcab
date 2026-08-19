<?php

declare(strict_types=1);

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    public function __construct(string $message = 'You do not have permission to access this resource.')
    {
        parent::__construct($message, 403);
    }
}
