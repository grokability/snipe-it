<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Translation\PotentiallyTranslatedString;

class DecryptableAttribute implements ValidationRule
{
    public function __construct(protected Model $model) {}

    /**
     * Run the validation rule.
     *
     * The value is already decrypted by the cast; inspect the underlying ciphertext instead.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ciphertext = $this->model->getAttributes()[$attribute] ?? null;
        if ($ciphertext === null || $ciphertext === '') {
            return;
        }

        try {
            Crypt::decrypt($ciphertext);
        } catch (DecryptException) {
            $fail(trans('general.something_went_wrong'));
        }
    }
}
