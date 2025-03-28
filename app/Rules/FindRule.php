<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * This is a better exists rule
 * @mixin Builder
 */
class FindRule extends DatabaseRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate($attribute, $value, $fail): void
    {
        $model = $this->source
            ->clone()
            ->where($this->column, $value)
            ->first();
        if (!$model) {
            $fail(__('validation.exists'));
        }
        $this->validator->setValue($attribute, $model);
    }
}
