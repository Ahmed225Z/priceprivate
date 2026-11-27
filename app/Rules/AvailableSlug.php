<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class AvailableSlug implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (in_array($value, config('egypt.reserved_slugs'), true)) {
            $fail('الرابط ده محجوز، اختار واحد تاني.');
        }
    }
}
