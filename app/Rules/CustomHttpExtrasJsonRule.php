<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validate the Custom HTTP adapter's Custom Extras JSON blob. Blank
 * is fine (no extras configured). Otherwise the value must parse as
 * a JSON array whose entries are objects containing at least the
 * required `key` and `path` string keys.
 */
class CustomHttpExtrasJsonRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
            return;
        }

        if (!is_string($value)) {
            $fail(trans('validation.custom_http_extras_json.not_json'));

            return;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail(trans('validation.custom_http_extras_json.not_json', [
                'error' => json_last_error_msg(),
            ]));

            return;
        }

        if (!is_array($decoded)) {
            $fail(trans('validation.custom_http_extras_json.not_array'));

            return;
        }

        foreach ($decoded as $index => $entry) {
            if (!is_array($entry)) {
                $fail(trans('validation.custom_http_extras_json.entry_not_object', ['index' => $index]));

                return;
            }
            foreach (['key', 'path'] as $requiredKey) {
                if (!array_key_exists($requiredKey, $entry) || !is_string($entry[$requiredKey]) || trim($entry[$requiredKey]) === '') {
                    $fail(trans('validation.custom_http_extras_json.missing_field', [
                        'index' => $index,
                        'field' => $requiredKey,
                    ]));

                    return;
                }
            }
        }
    }
}
