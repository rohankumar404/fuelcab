<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FuelQuantity implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value) || $value <= 0) {
            $fail('The :attribute must be a positive number.');
            return;
        }

        // Example FuelCab threshold checks: e.g. maximum order size of 5000 Liters
        if ($value > 5000) {
            $fail('The :attribute exceeds the maximum delivery capacity of 5,000 Liters.');
        }
    }
}
