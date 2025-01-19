<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

/**
 * This is a better exists rule
 * @mixin Builder
 */
abstract class DatabaseRule implements ValidationRule, ValidatorAwareRule
{
    protected Validator $validator;

    public static function make(string|Builder $source, string $column = 'id'): static
    {
        if (is_string($source)) {
            if (class_exists($source) && is_callable([$source, 'query'])) {
                $source = call_user_func([$source, 'query']);
            } else {
                $source = DB::table($source);
            }
        }
        return new static($source, $column);
    }

    public function __construct(
        protected readonly Builder $source,
        protected readonly string $column = 'id'
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     * @return void
     */
    abstract function validate(string $attribute, mixed $value, Closure $fail): void;

    public function setValidator(Validator $validator): void
    {
        $this->validator = $validator;
    }

    public function __call(string $name, array $arguments)
    {
        $res = call_user_func_array([$this->source, $name], $arguments);
        return $res === $this->source ? $this : $res;
    }
}
