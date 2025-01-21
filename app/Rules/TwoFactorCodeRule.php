<?php

namespace App\Rules;

use App\Lib\Google2FA\Authenticator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TwoFactorCodeRule implements ValidationRule
{
    public function __construct(protected string $secret)
    {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^\d{6}$/', $value)) {
            $fail("The :attribute must be a 6 digit number.");
        }
        Authenticator::verify($value, $this->secret) || $fail("The :attribute is invalid.");
    }
}
