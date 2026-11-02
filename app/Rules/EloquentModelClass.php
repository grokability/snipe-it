<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Validates that a string is the FQCN of a loadable Eloquent model.
 */
class EloquentModelClass implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! class_exists($value) || ! is_subclass_of($value, Model::class)) {
            $fail(trans('validation.eloquent_model_class'));
        }
    }
}
