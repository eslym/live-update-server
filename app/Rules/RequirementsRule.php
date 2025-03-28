<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

class RequirementsRule implements ValidationRule, ValidatorAwareRule
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
            'min' => ['nullable', 'integer', 'min:0'],
            'max' => ['nullable', 'integer', 'min:0'],
        ]);
        if ($sub->fails()) {
            foreach ($sub->errors()->toArray() as $key => $errors) {
                foreach ($errors as $error) {
                    $this->validator->errors()->add($attribute . '.' . $key, $error);
                }
            }
        }
        if (
            ($value['min'] ?? false) &&
            ($value['max'] ?? false) &&
            $value['max'] <= $value['min']
        ) {
            $this->validator->errors()->add($attribute . '.max', 'Must be greater than minimum version.');
        }
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }
}
