<?php

namespace App\Rules;

use Closure;
use Composer\Semver\Semver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use UnexpectedValueException;

class SemverRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            Semver::sort([$value]);
        } catch (UnexpectedValueException) {
            $fail("The :attribute must be a valid Semver version.");
        }
    }
}
