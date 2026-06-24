<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     * Accepts E.164 format: +91XXXXXXXXXX or 10-digit local numbers.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = '/^\+?[1-9]\d{9,14}$/';

        if (! preg_match($pattern, (string) $value)) {
            $fail("The {$attribute} must be a valid phone number (E.164 format).");
        }
    }
}
