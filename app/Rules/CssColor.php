<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CssColor implements ValidationRule
{
    private static function pattern(): string
    {
        $num = '\s*[\d.]+\s*';
        $pct = '\s*[\d.]+%\s*';
        $alpha = '(?:,\s*[\d.]+\s*)?';
        $hex = '#[0-9a-fA-F]{3,8}';
        $rgb = "rgba?\({$num},{$num},{$num}{$alpha}\)";
        $hsl = "hsla?\({$num},{$pct},{$pct}{$alpha}\)";

        return "/^(?:{$hex}|{$rgb}|{$hsl})$/i";
    }

    /**
     * Return $value if it is a safe CSS color, otherwise return $default.
     * Use this for defense-in-depth when rendering color values already in the database.
     */
    public static function sanitize(?string $value, string $default): string
    {
        if ($value && preg_match(self::pattern(), trim($value))) {
            return $value;
        }

        return $default;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match(self::pattern(), $value)) {
            $fail(trans('validation.valid_css_color'));
        }
    }
}
