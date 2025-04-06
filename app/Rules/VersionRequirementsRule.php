<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

class VersionRequirementsRule implements ValidationRule, ValidatorAwareRule
{
    protected Validator $validator;

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail(__('validation.array'));
            return;
        }
        $sub = validator($value, [
            'android' => ['nullable', 'array', new RequirementsRule()],
            'ios' => ['nullable', 'array', new RequirementsRule()],
        ]);
        if ($sub->fails()) {
            foreach ($sub->errors()->toArray() as $key => $errors) {
                foreach ($errors as $error) {
                    $this->validator->errors()->add($attribute . '.' . $key, $error);
                }
            }
        }
        if (!is_array($value['android'] ?? null) && !is_array($value['ios'] ?? null)) {
            $fail("At least one platform must be specified");
        }
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }
}
