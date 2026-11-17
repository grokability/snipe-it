<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SnipeCrypt implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * Unused parameters are required by Laravel's CastsAttributes contract.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            return 'Error Decrypting: '.$exception->getMessage();
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * Unused parameters are required by Laravel's CastsAttributes contract.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $value = is_array($value) ? implode(', ', $value) : $value;

        if (isset($attributes[$key]) && $attributes[$key] !== '') {
            try {
                $original = Crypt::decrypt($attributes[$key]);
                if ($original === $value) {
                    return $attributes[$key];
                }
            } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                // A replacement value can repair an unreadable encrypted field.
            }
        }

        return $value === null || $value === '' ? $value : Crypt::encrypt($value);
    }
}
