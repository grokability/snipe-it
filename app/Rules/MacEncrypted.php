<?php

namespace App\Rules;

use App\Models\CustomField;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class MacEncrypted implements ValidationRule
{
    use ValidatesAttributes;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $attributeName = trim(preg_replace('/_+|snipeit|\d+/', ' ', $attribute));
            $decrypted = Crypt::decrypt($value);
            if (!$this->validateRegex($attributeName, $decrypted, ['/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/']) && !is_null($decrypted)) {
                $fail(trans('validation.mac_address', ['attribute' => $attributeName]));
            }
        } catch (\Exception $e) {
            report($e);
            $fail(trans('general.something_went_wrong'));
        }
    }
}
