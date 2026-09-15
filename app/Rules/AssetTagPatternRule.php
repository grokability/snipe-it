<?php

namespace App\Rules;

use App\SyncAdapters\SyncHostFromAdapter;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Non-empty asset_tag_pattern must contain at least one recognized
 * placeholder (e.g. {external_id}, {serial}). Without a placeholder
 * the pattern renders to the same literal string for every record
 * and the first save wins on asset_tag uniqueness while every
 * subsequent record collides.
 *
 * Empty / null values are allowed. The pattern is optional, and its
 * absence falls back to Snipe-IT's auto-increment or a
 * source-slug + external-id composite.
 */
class AssetTagPatternRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || $value === '') {
            return;
        }

        foreach (SyncHostFromAdapter::ASSET_TAG_PATTERN_PLACEHOLDERS as $placeholder) {
            if (str_contains($value, $placeholder)) {
                return;
            }
        }

        $fail(trans('validation.asset_tag_pattern_placeholder', [
            'placeholders' => implode(', ', SyncHostFromAdapter::ASSET_TAG_PATTERN_PLACEHOLDERS),
        ]));
    }
}
