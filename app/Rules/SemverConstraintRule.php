<?php

namespace App\Rules;

use Closure;
use Composer\Semver\Semver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use UnexpectedValueException;

class SemverConstraintRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            Semver::satisfies('1.0.0', $value);
        } catch (UnexpectedValueException) {
            $fail("The :attribute must be a valid semver constraint.");
        }
    }
}
