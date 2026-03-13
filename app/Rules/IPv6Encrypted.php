<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class IPv6Encrypted implements ValidationRule
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
            if (!$this->validateIpv6($attributeName, $decrypted) && !is_null($decrypted)) {
                $fail(trans('validation.ipv6', ['attribute' => $attributeName]));
            }
        } catch (\Exception $e) {
            report($e);
            $fail(trans('general.something_went_wrong'));
        }
    }
}
