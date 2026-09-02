<?php

namespace App\Models\Labels\CustomLabels\Concerns;


trait HasCustomLabelEditorSections
{
    public function getEditorSections(): array
    {
        return array_replace_recursive(
            $this->baseEditorSections(),
            $this->layoutEditorSections()
        );
    }

    protected function baseEditorSections(): array
    {
        return [
            'supports' => [
                'label' => trans('admin/labels/general.sections.supports'),
                'column_span' => 2,
                'display' => 'inline',
                'fields' => [
                    'fields' => [
                        'type' => 'number',
                        'label' => trans('admin/labels/general.fields.fields'),
                    ],
                    'asset_tag' => [
                        'type' => 'checkbox',
                        'label' => trans('admin/labels/general.fields.asset_tag'),
                    ],
                    'barcode_1d' => [
                        'type' => 'checkbox',
                        'label' => trans('admin/labels/general.fields.barcode_1d'),
                    ],
                    'barcode_2d' => [
                        'type' => 'checkbox',
                        'label' => trans('admin/labels/general.fields.barcode_2d'),
                    ],
                    'logo' => [
                        'type' => 'checkbox',
                        'label' => trans('admin/labels/general.fields.logo'),
                    ],
                    'title' => [
                        'type' => 'checkbox',
                        'label' => trans('admin/labels/general.fields.title'),
                    ],
                ],
            ],
            'content' => [
                'label' => trans('admin/labels/general.sections.content'),
                'column_span' => 2,
                'groups' => [
                    'barcode_1d' => [
                        'label' => trans('admin/labels/general.groups.barcode_1d'),
                        'toggle' => 'supports.barcode_1d',
                        'fields' => [
                            'barcode_size' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.barcode_size'),
                            ],
                            'barcode_margin' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.barcode_margin'),
                            ],
                            'barcode1D_v_align' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.barcode1D_v_align'),
                                'options' => [
                                    'T' => trans('admin/labels/general.options.top'),
                                    'M' => trans('admin/labels/general.options.middle'),
                                    'B' => trans('admin/labels/general.options.bottom'),
                                ],
                            ],
                        ],
                    ],

                    'barcode_2d' => [
                        'label' => trans('admin/labels/general.groups.barcode_2d'),
                        'toggle' => 'supports.barcode_2d',
                        'fields' => [
                            'barcode_2d_size' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.barcode_2d_size'),
                            ],
                            'barcode2D_h_align' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.barcode2D_h_align'),
                                'options' => [
                                    'L' => trans('admin/labels/general.options.left'),
                                    'C' => trans('admin/labels/general.options.center'),
                                    'R' => trans('admin/labels/general.options.right'),
                                ],
                            ],
                            'barcode2D_v_align' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.barcode2D_v_align'),
                                'options' => [
                                    'T' => trans('admin/labels/general.options.top'),
                                    'M' => trans('admin/labels/general.options.middle'),
                                    'B' => trans('admin/labels/general.options.bottom'),
                                ],
                            ],
                        ],
                    ],

                    'tag' => [
                        'label' => trans('admin/labels/general.groups.tag'),
                        'toggle' => 'supports.asset_tag',
                        'fields' => [
                            'tag_font' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.tag_font'),
                                'options' => [
                                    'freemono' => trans('admin/labels/general.options.freemono'),
                                    'freesans' => trans('admin/labels/general.options.freesans'),
                                    'dejavusans' => trans('admin/labels/general.options.dejavusans'),
                                ],
                            ],
                            'tag_font_size' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.tag_font_size'),
                            ],
                            'tag_alignment' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.tag_alignment'),
                                'options' => [
                                    'L' => trans('admin/labels/general.options.left'),
                                    'C' => trans('admin/labels/general.options.center'),
                                    'R' => trans('admin/labels/general.options.right'),
                                ],
                            ],
                            'tag_offset_x' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.tag_offset_x'),
                            ],
                            'tag_offset_y' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.tag_offset_y'),
                            ],
                        ],
                    ],
                    'logo' => [
                        'label' => trans('admin/labels/general.groups.logo'),
                        'toggle' => 'supports.logo',
                        'fields' => [
                            'logo_max_width' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.logo_max_width'),
                            ],
                            'logo_margin' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.logo_margin'),
                            ],
                            'logo_h_align' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.logo_h_align'),
                                'options' => [
                                    'L' => trans('admin/labels/general.options.left'),
                                    'C' => trans('admin/labels/general.options.center'),
                                    'R' => trans('admin/labels/general.options.right'),
                                ],
                            ],
                        ],
                    ],

                    'title' => [
                        'label' => trans('admin/labels/general.groups.title'),
                        'toggle' => 'supports.title',
                        'fields' => array_filter([
                            'title_font' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.title_font'),
                                'options' => [
                                    'freemono' => trans('admin/labels/general.options.freemono'),
                                    'freesans' => trans('admin/labels/general.options.freesans'),
                                    'dejavusans' => trans('admin/labels/general.options.dejavusans'),
                                ],
                            ],

                            'title_font_size' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.title_font_size'),
                            ],

                            'title_margin' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.title_margin'),
                            ],

                            'title_offset_x' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.title_offset_x'),
                            ],

                            'title_position' => !$this->isTapeLabel()
                                ? [
                                    'type' => 'select',
                                    'label' => trans('admin/labels/general.fields.title_position'),
                                    'options' => [
                                        'inline' => trans('admin/labels/general.options.inline'),
                                        'top' => trans('admin/labels/general.options.top'),
                                    ],
                                ]
                                : null,
                        ]),
                    ],

                    'field_labels' => [
                        'label' => trans('admin/labels/general.groups.field_labels'),
                        'toggle' => 'supports.fields',
                        'fields' => [
                            'field_label_font' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.field_label_font'),
                                'options' => [
                                    'freemono' => trans('admin/labels/general.options.freemono'),
                                    'freesans' => trans('admin/labels/general.options.freesans'),
                                    'dejavusans' => trans('admin/labels/general.options.dejavusans'),
                                ],
                            ],
                            'field_label_font_size' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.field_label_font_size'),
                            ],
                            'field_label_margin' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.field_label_margin'),
                            ],
                        ],
                    ],

                    'field_values' => [
                        'label' => trans('admin/labels/general.groups.field_values'),
                        'toggle' => 'supports.fields',
                        'fields' => [
                            'field_value_font' => [
                                'type' => 'select',
                                'label' => trans('admin/labels/general.fields.field_value_font'),
                                'options' => [
                                    'freemono' => trans('admin/labels/general.options.freemono'),
                                    'freesans' => trans('admin/labels/general.options.freesans'),
                                    'dejavusans' => trans('admin/labels/general.options.dejavusans'),
                                ],
                            ],
                            'field_value_font_size' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.field_value_font_size'),
                            ],
                            'field_value_margin' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.field_value_margin'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function layoutEditorSections(): array
    {
        if ($this->isTapeLabel()) {
            return $this->tapeLayoutEditorSections();
        }

        return $this->sheetLayoutEditorSections();
    }

    protected function isTapeLabel(): bool
    {
        return str_contains(static::class, 'Tape')
            || method_exists($this, 'getTapeWidth')
            || method_exists($this, 'getTapeHeight');
    }

    protected function sheetLayoutEditorSections(): array
    {
        return [
            'layout' => [
                'label' => trans('admin/labels/general.sections.layout'),
                'column_span' => 2,
                'groups' => [
                    'page' => [
                        'label' => trans('admin/labels/general.sections.page'),
                        'section_key' => 'page',
                        'fields' => [
                            'width' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.width'),
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.height'),
                            ],
                            'margin_top' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.margin_top'),
                            ],
                            'margin_right' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.margin_right'),
                            ],
                            'margin_bottom' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.margin_bottom'),
                            ],
                            'margin_left' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.margin_left'),
                            ],
                        ],
                    ],

                    'grid' => [
                        'label' => trans('admin/labels/general.sections.grid'),
                        'section_key' => 'grid',
                        'fields' => [
                            'columns' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.columns'),
                            ],
                            'rows' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.rows'),
                            ],
                            'column_spacing' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.column_spacing'),
                            ],
                            'row_spacing' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.row_spacing'),
                            ],
                        ],
                    ],

                    'label' => [
                        'label' => trans('admin/labels/general.sections.label'),
                        'section_key' => 'label',
                        'fields' => [
                            'width' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.width'),
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.height'),
                            ],
                            'border' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.border'),
                            ],
                            'padding_top' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.padding_top'),
                            ],
                            'padding_right' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.padding_right'),
                            ],
                            'padding_bottom' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.padding_bottom'),
                            ],
                            'padding_left' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.padding_left'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function tapeLayoutEditorSections(): array
    {
        return [
            'layout' => [
                'label' => trans('admin/labels/general.sections.layout'),
                'column_span' => 2,
                'groups' => [
                    'label' => [
                        'label' => trans('admin/labels/general.sections.label'),
                        'section_key' => 'dimensions',
                        'fields' => [
                            'width' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.width'),
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.height'),
                            ],
                            'label_gap' => [
                                'type' => 'number',
                                'label' => trans('admin/labels/general.fields.label_gap'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}