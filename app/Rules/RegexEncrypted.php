<?php

namespace App\Rules;

use App\Models\CustomField;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class RegexEncrypted implements ValidationRule
{
    use ValidatesAttributes;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $field = CustomField::where('db_column', $attribute)->first();
        $regex = $field->format;
        $regex = str_replace('regex:', '', $regex);
        try {
            $attributeName = trim(preg_replace('/_+|snipeit|\d+/', ' ', $attribute));
            $decrypted = Crypt::decrypt($value);
            if (!$this->validateRegex($attributeName, $decrypted, [$regex]) && !is_null($decrypted)) {
                $fail(trans('validation.regex', ['attribute' => $attributeName]));
            }
        } catch (\Exception $e) {
            report($e->getMessage());
            $fail(trans('general.something_went_wrong'));
        }
    }
}
