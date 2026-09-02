<?php

namespace App\Models\Labels;

final class LabelGeometryRules
{
    public static function sheet(string $prefix = ''): array
    {
        return [
            "{$prefix}grid.rows" => ['required', 'integer', 'min:1'],
            "{$prefix}grid.columns" => ['required', 'integer', 'min:1'],
            "{$prefix}grid.row_spacing" => ['required', 'numeric', 'min:0'],
            "{$prefix}grid.column_spacing" => ['required', 'numeric', 'min:0'],
            "{$prefix}label.width" => ['required', 'numeric', 'gt:0'],
            "{$prefix}label.height" => ['required', 'numeric', 'gt:0'],
            "{$prefix}label.border" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}label.padding_top" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}label.padding_right" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}label.padding_bottom" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}label.padding_left" => ['nullable', 'numeric', 'min:0'],

            "{$prefix}page.width" => ['required', 'numeric', 'gt:0'],
            "{$prefix}page.height" => ['required', 'numeric', 'gt:0'],
            "{$prefix}page.margin_top" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}page.margin_right" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}page.margin_bottom" => ['nullable', 'numeric', 'min:0'],
            "{$prefix}page.margin_left" => ['nullable', 'numeric', 'min:0'],
        ];
    }
}