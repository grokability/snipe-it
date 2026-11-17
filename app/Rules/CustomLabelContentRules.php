<?php

namespace App\Rules;

use App\Models\Labels\CustomLabelFonts;
use Illuminate\Validation\Rule;

class CustomLabelContentRules
{
    public static function shared(): array
    {
        return [
            'content.barcode_size' => ['required', 'numeric'],
            'content.barcode_margin' => ['required', 'numeric'],

            'content.barcode_2d_size' => ['required', 'numeric'],
            'content.barcode2D_h_align' => ['required', 'string', 'in:L,C,R'],
            'content.barcode2D_v_align' => ['required', 'string', 'in:T,C,B'],

            'content.tag_alignment' => ['required', 'string', 'in:L,C,R'],

            'content.logo_max_width' => ['sometimes', 'numeric'],
            'content.logo_margin' => ['sometimes', 'numeric'],
            'content.logo_h_align' => ['sometimes', 'string', 'in:L,C,R'],
            'content.logo_v_align' => ['sometimes', 'string', 'in:T,C,B'],

            'content.tag_font_size' => ['required', 'numeric'],
            'content.tag_offset_x' => ['required', 'numeric'],
            'content.tag_offset_y' => ['required', 'numeric'],

            'content.title_font_size' => ['required', 'numeric'],
            'content.title_margin' => ['required', 'numeric'],
            'content.title_offset_x' => ['required', 'numeric'],

            'content.field_label_font_size' => ['required', 'numeric'],
            'content.field_label_margin' => ['required', 'numeric'],
            'content.field_value_font_size' => ['required', 'numeric'],
            'content.field_value_margin' => ['required', 'numeric'],

            'content.tag_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED)],
            'content.title_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED)],
            'content.field_label_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED)],
            'content.field_value_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED)],
        ];
    }

    public static function tape(): array
    {
        return [
            'dimensions' => ['required', 'array'],
            'dimensions.width' => ['required', 'numeric', 'gt:0'],
            'dimensions.height' => ['required', 'numeric', 'gt:0'],
            'dimensions.label_gap' => ['nullable', 'numeric', 'min:0'],

            'content.barcode1D_v_align' => ['nullable', 'string', 'in:T,M,B'],
            'content.barcode1D_placement' => ['nullable', 'string', 'in:full_width,text_column,inline'],

            'content.barcode2D_placement' => ['nullable', 'string', 'in:stacked,inline,text_column'],
            'content.barcode_2d_margin' => ['nullable', 'numeric'],

            'content.text_size_mod' => ['nullable', 'numeric'],
            'content.text_area_offset_y' => ['nullable', 'numeric'],

            'content.logo_max_height' => ['nullable', 'numeric'],
            'content.logo_placement' => ['nullable', 'string', 'in:inline,text_column'],

            'content.tag_position_mode' => ['nullable', 'string', 'in:free,inline'],
            'content.field_alignment' => ['nullable', 'string', 'in:L,C,R'],

            'content.text_render_mode' => ['nullable', 'string', 'in:inline,block,vertical_stack'],
        ];
    }
}