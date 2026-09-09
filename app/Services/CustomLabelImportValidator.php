<?php

namespace App\Services;

use App\Models\Labels\LabelGeometryRules;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Models\Labels\CustomLabelFonts;
class CustomLabelImportValidator
{
    public function validate(?string $rawJson): array
    {
        $config = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
            throw ValidationException::withMessages([
                'config_snapshot' => 'The imported label config must be valid JSON.',
            ]);
        }

        validator($config, $this->rules($config), [], $this->attributes())->validate();

        return $config;
    }

    protected function rules(array $config): array
    {
        return match ($config['type'] ?? null) {
            'sheet' => array_merge(
                $this->baseRules(),
                $this->sharedContentRules(),
                $this->sheetRules(),
            ),

            'tape' => array_merge(
                $this->baseRules(),
                $this->sharedContentRules(),
                $this->printableAreaRules(),
                $this->tapeContentRules(),
            ),

            default => $this->baseRules(),
        };
    }

    protected function baseRules(): array
    {
        return [
            'unit' => ['required', 'string', 'in:mm'],
            'template' => ['required', 'string'],
            'type' => ['required', 'string', 'in:sheet,tape'],
            'name' => ['required', 'string'],
            'content' => ['required', 'array'],
            'supports' => ['required', 'array'],
            'supports.asset_tag' => ['required', 'boolean'],
            'supports.barcode_1d' => ['required', 'boolean'],
            'supports.barcode_2d' => ['required', 'boolean'],
            'supports.fields' => ['required', 'integer'],
            'supports.logo' => ['required', 'boolean'],
            'supports.title' => ['required', 'boolean'],
        ];
    }

    protected function printableAreaRules(): array
    {
        return [
            'printable_area' => ['required', 'array'],
            'printable_area.x1' => ['required', 'numeric'],
            'printable_area.y1' => ['required', 'numeric'],
            'printable_area.x2' => ['required', 'numeric'],
            'printable_area.y2' => ['required', 'numeric'],
            'printable_area.width' => ['required', 'numeric'],
            'printable_area.height' => ['required', 'numeric'],
        ];
    }

    protected function sharedContentRules(): array
    {
        return [
            'content.barcode_size' => ['required', 'numeric'],
            'content.barcode_margin' => ['required', 'numeric'],

            'content.barcode_2d_size' => ['required', 'numeric'],
            'content.barcode2D_h_align' => ['required', 'string', 'in:L,C,R'],
            'content.barcode2D_v_align' => ['required', 'string', 'in:T,C,B'],

            'content.tag_alignment' => ['required', 'string', 'in:L,C,R'],

            'content.logo_max_width' => ['required', 'numeric'],
            'content.logo_margin' => ['required', 'numeric'],
            'content.logo_h_align' => ['required', 'string', 'in:L,C,R'],
            'content.logo_v_align' => ['required', 'string', 'in:T,C,B'],

            'content.tag_font_size' => ['required', 'numeric'],
            'content.tag_offset_x' => ['required', 'numeric'],
            'content.tag_offset_y' => ['required', 'numeric'],

            'content.title_font_size' => ['required', 'numeric'],
            'content.title_margin' => ['required', 'numeric'],

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

    protected function tapeContentRules(): array
    {
        return [
            'printable_area' => ['required', 'array'],
            
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

    protected function sheetRules(): array
    {
        return array_merge(
            $this->printableAreaRules(),
            LabelGeometryRules::sheet(),
            [
                /*
                |--------------------------------------------------------------------------
                | Page
                |--------------------------------------------------------------------------
                */
                'page' => ['required', 'array'],
                'page.width' => ['required', 'numeric', 'gt:0'],
                'page.height' => ['required', 'numeric', 'gt:0'],
                'page.margin_top' => ['required', 'numeric'],
                'page.margin_right' => ['required', 'numeric'],
                'page.margin_bottom' => ['required', 'numeric'],
                'page.margin_left' => ['required', 'numeric'],

                /*
                |--------------------------------------------------------------------------
                | Grid
                |--------------------------------------------------------------------------
                */
                'grid' => ['required', 'array'],
                'grid.columns' => ['required', 'integer', 'min:1'],
                'grid.rows' => ['required', 'integer', 'min:1'],
                'grid.row_spacing' => ['required', 'numeric', 'min:0'],
                'grid.column_spacing' => ['required', 'numeric', 'min:0'],

                /*
                |--------------------------------------------------------------------------
                | Label
                |--------------------------------------------------------------------------
                */
                'label' => ['required', 'array'],
                'label.width' => ['required', 'numeric', 'gt:0'],
                'label.height' => ['required', 'numeric', 'gt:0'],
                'label.border' => ['required', 'numeric', 'min:0'],
                'label.padding_top' => ['required', 'numeric', 'min:0'],
                'label.padding_right' => ['required', 'numeric', 'min:0'],
                'label.padding_bottom' => ['required', 'numeric', 'min:0'],
                'label.padding_left' => ['required', 'numeric', 'min:0'],

                /*
                |--------------------------------------------------------------------------
                | Sheet-only Content
                |--------------------------------------------------------------------------
                */
                'content.title_offset_x' => ['required', 'numeric'],
                'content.text_area_width' => ['nullable', 'numeric'],
                'content.text_area_height' => ['nullable', 'numeric'],
            ]
        );
    }

    public function normalizeFonts(array $config): array
    {
        foreach ([
                     'tag_font',
                     'title_font',
                     'field_label_font',
                     'field_value_font',
                 ] as $key) {
            $font = data_get($config, "content.{$key}");

            if (
                $font !== null
                && !in_array($font, CustomLabelFonts::ALLOWED, true)
            ) {
                data_set($config, "content.{$key}", 'freesans');
            }
        }

        return $config;
    }

    protected function attributes(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Page
            |--------------------------------------------------------------------------
            */
            'page.width' => 'page width',
            'page.height' => 'page height',
            'page.margin_top' => 'page top margin',
            'page.margin_right' => 'page right margin',
            'page.margin_bottom' => 'page bottom margin',
            'page.margin_left' => 'page left margin',

            /*
            |--------------------------------------------------------------------------
            | Grid
            |--------------------------------------------------------------------------
            */
            'grid.columns' => 'grid columns',
            'grid.rows' => 'grid rows',
            'grid.column_spacing' => 'grid column spacing',
            'grid.row_spacing' => 'grid row spacing',

            /*
            |--------------------------------------------------------------------------
            | Printable Area
            |--------------------------------------------------------------------------
            */
            'printable_area.x1' => 'printable area x1',
            'printable_area.y1' => 'printable area y1',
            'printable_area.x2' => 'printable area x2',
            'printable_area.y2' => 'printable area y2',
            'printable_area.width' => 'printable area width',
            'printable_area.height' => 'printable area height',

            /*
            |--------------------------------------------------------------------------
            | Label
            |--------------------------------------------------------------------------
            */
            'label.width' => 'label width',
            'label.height' => 'label height',
            'label.border' => 'label border',
            'label.padding_top' => 'label top padding',
            'label.padding_right' => 'label right padding',
            'label.padding_bottom' => 'label bottom padding',
            'label.padding_left' => 'label left padding',

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */
            'content.barcode_size' => 'barcode size',
            'content.barcode_margin' => 'barcode margin',

            'content.barcode1D_v_align' => '1D barcode vertical alignment',
            'content.barcode1D_placement' => '1D barcode placement',

            'content.barcode2D_h_align' => '2D barcode horizontal alignment',
            'content.barcode2D_v_align' => '2D barcode vertical alignment',
            'content.barcode2D_placement' => '2D barcode placement',

            'content.tag_alignment' => 'tag alignment',
            'content.tag_position_mode' => 'tag position mode',

            'content.barcode_2d_size' => '2D barcode size',
            'content.barcode_2d_margin' => '2D barcode margin',

            'content.logo_max_width' => 'logo max width',
            'content.logo_max_height' => 'logo max height',
            'content.logo_margin' => 'logo margin',
            'content.logo_h_align' => 'logo horizontal alignment',
            'content.logo_v_align' => 'logo vertical alignment',
            'content.logo_placement' => 'logo placement',

            'content.tag_font_size' => 'tag font size',
            'content.tag_offset_x' => 'tag horizontal offset',
            'content.tag_offset_y' => 'tag vertical offset',

            'content.title_font_size' => 'title font size',
            'content.title_margin' => 'title margin',
            'content.title_offset_x' => 'title horizontal offset',

            'content.field_label_font_size' => 'field label font size',
            'content.field_label_margin' => 'field label margin',

            'content.field_value_font_size' => 'field value font size',
            'content.field_value_margin' => 'field value margin',

            'content.field_alignment' => 'field alignment',

            'content.text_size_mod' => 'text size modifier',
            'content.text_area_width' => 'text area width',
            'content.text_area_height' => 'text area height',
            'content.text_area_offset_y' => 'text area vertical offset',
            'content.text_render_mode' => 'text render mode',

            /*
             |--------------------------------------------------------------------------
             | Fonts
             |--------------------------------------------------------------------------
             */
            'content.tag_font' => 'tag font',
            'content.title_font' => 'title font',
            'content.field_label_font' => 'field label font',
            'content.field_value_font' => 'field value font',

            /*
            |--------------------------------------------------------------------------
            | Supports
            |--------------------------------------------------------------------------
            */
            'supports.asset_tag' => 'supports asset tag',
            'supports.barcode_1d' => 'supports 1D barcode',
            'supports.barcode_2d' => 'supports 2D barcode',
            'supports.fields' => 'supports fields',
            'supports.logo' => 'supports logo',
            'supports.title' => 'supports title',
        ];
    }
}