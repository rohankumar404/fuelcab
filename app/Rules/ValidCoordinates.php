<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCoordinates implements ValidationRule
{
    /**
     * Validates that the value is an array with valid lat/lng.
     *
     * Expects: ['lat' => float, 'lng' => float]
     * or individual fields: validate lat and lng separately.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)
            || ! isset($value['lat'], $value['lng'])
            || ! is_numeric($value['lat'])
            || ! is_numeric($value['lng'])
        ) {
            $fail("The {$attribute} must contain valid 'lat' and 'lng' coordinates.");
            return;
        }

        $lat = (float) $value['lat'];
        $lng = (float) $value['lng'];

        if ($lat < -90 || $lat > 90) {
            $fail("The {$attribute}.lat must be between -90 and 90.");
        }

        if ($lng < -180 || $lng > 180) {
            $fail("The {$attribute}.lng must be between -180 and 180.");
        }
    }
}
