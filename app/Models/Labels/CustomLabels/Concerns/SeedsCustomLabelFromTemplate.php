<?php

namespace App\Models\Labels\CustomLabels\Concerns;

trait SeedsCustomLabelFromTemplate
{
    protected function unitConverterFor($template): callable
    {
        $sourceUnit = $template->getUnit();

        return function ($value) use ($sourceUnit) {
            if ($value === null || $value === '') {
                return $value;
            }

            return $sourceUnit === 'in' && is_numeric($value)
                ? (float)$value * 25.4
                : $value;
        };
    }

    protected function seedSheetMeasurements($template, callable $convert): void
    {
        $measurementMap = [
            'pageWidth' => 'getPageWidth',
            'pageHeight' => 'getPageHeight',
            'pageMarginTop' => 'getPageMarginTop',
            'pageMarginRight' => 'getPageMarginRight',
            'pageMarginBottom' => 'getPageMarginBottom',
            'pageMarginLeft' => 'getPageMarginLeft',

            'labelWidth' => 'getLabelWidth',
            'labelHeight' => 'getLabelHeight',
            'labelRowSpacing' => 'getLabelRowSpacing',
            'labelColumnSpacing' => 'getLabelColumnSpacing',
            'labelMarginTop' => 'getLabelMarginTop',
            'labelMarginRight' => 'getLabelMarginRight',
            'labelMarginBottom' => 'getLabelMarginBottom',
            'labelMarginLeft' => 'getLabelMarginLeft',
        ];

        foreach ($measurementMap as $property => $method) {
            if (method_exists($template, $method)) {
                $this->{$property} = $convert($template->{$method}());
            }
        }
    }

    protected function seedTapeMeasurements($template, callable $convert): void
    {
        $measurementMap = [
            'width' => 'getWidth',
            'height' => 'getHeight',
            'marginTop' => 'getMarginTop',
            'marginRight' => 'getMarginRight',
            'marginBottom' => 'getMarginBottom',
            'marginLeft' => 'getMarginLeft',
        ];

        foreach ($measurementMap as $property => $method) {
            if (method_exists($template, $method)) {
                $this->{$property} = (float)$convert($template->{$method}());
            }
        }
    }

    protected function seedSheetGrid($template): void
    {
        if (method_exists($template, 'getRows')) {
            $this->rows = (int)$template->getRows();
        }

        if (method_exists($template, 'getColumns')) {
            $this->columns = (int)$template->getColumns();
        }
    }

    protected function seedSupportsFromTemplate($template): void
    {
        $supportMap = [
            'supportAssetTag' => 'getSupportAssetTag',
            'support1DBarcode' => 'getSupport1DBarcode',
            'support2DBarcode' => 'getSupport2DBarcode',
            'supportFields' => 'getSupportFields',
            'supportLogo' => 'getSupportLogo',
            'supportTitle' => 'getSupportTitle',
        ];

        foreach ($supportMap as $property => $method) {
            if (method_exists($template, $method)) {
                $this->{$property} = $template->{$method}();
            }
        }
    }

    protected function seedLegacyContentFromTemplate($template, callable $convert): void
    {
        $legacyContentMap = [
            'barcodeSize' => ['getBarcodeSize', 'getBarcode1DSize'],
            'barcode2DSize' => ['get2DBarcodeSize', 'getBarcode2DSize'],
            'barcodeMargin' => ['getBarcodeMargin'],
            'barcode2Margin' => ['getBarcode2DMargin'],
            'logoMaxWidth' => ['getLogoMaxWidth'],
            'logoMargin' => ['getLogoMargin'],
            'tagSize' => ['getTagSize'],
            'titleSize' => ['getTitleSize'],
            'labelSize' => ['getLabelSize'],
            'fieldSize' => ['getFieldSize'],
            'titleMargin' => ['getTitleMargin'],
            'labelMargin' => ['getLabelMargin'],
            'fieldMargin' => ['getFieldMargin'],
        ];

        foreach ($legacyContentMap as $property => $methods) {
            foreach ($methods as $method) {
                if (method_exists($template, $method)) {
                    $this->{$property} = $convert($template->{$method}());
                    break;
                }
            }
        }

        if (method_exists($template, 'getLogoSize') && !method_exists($template, 'getLogoMaxWidth')) {
            $logoSize = $template->getLogoSize();

            $this->logoMaxWidth = isset($logoSize[0])
                ? $convert($logoSize[0])
                : $this->logoMaxWidth;
        }

        if (method_exists($template, 'getTextSize')) {
            $textSize = $convert($template->getTextSize());

            foreach ([
                         'tagSize' => 'getTagSize',
                         'titleSize' => 'getTitleSize',
                         'labelSize' => 'getLabelSize',
                         'fieldSize' => 'getFieldSize',
                     ] as $property => $method) {
                if (!method_exists($template, $method)) {
                    $this->{$property} = $textSize;
                }
            }
        }

        if (method_exists($template, 'getTextMargin')) {
            $textMargin = $convert($template->getTextMargin());

            foreach ([
                         'titleMargin' => 'getTitleMargin',
                         'labelMargin' => 'getLabelMargin',
                         'fieldMargin' => 'getFieldMargin',
                     ] as $property => $method) {
                if (!method_exists($template, $method)) {
                    $this->{$property} = $textMargin;
                }
            }
        }
    }

    protected function seedEditorContentFromTemplate($template, callable $convert): void
    {
        $content = method_exists($template, 'getEditorConfigSections')
            ? ($template->getEditorConfigSections()['content'] ?? [])
            : [];

        foreach ($this->editorNumericContentMap() as $key => $property) {
            if (array_key_exists($key, $content)) {
                $this->{$property} = $convert($content[$key]);
            }
        }

        foreach ($this->editorStringContentMap() as $key => $property) {
            if (array_key_exists($key, $content)) {
                $this->{$property} = (string)$content[$key];
            }
        }
    }

    protected function editorNumericContentMap(): array
    {
        return [
            'barcode_size' => 'barcodeSize',
            'barcode_margin' => 'barcodeMargin',
            'barcode_2d_size' => 'barcode2DSize',
            'barcode_2d_margin' => 'barcode2DMargin', //Tape only

            'tag_font_size' => 'tagSize',
            'tag_offset_x' => 'tagOffsetX',
            'tag_offset_y' => 'tagOffsetY',

            'title_font_size' => 'titleSize',
            'title_margin' => 'titleMargin',
            'title_offset_x' => 'titleOffsetX',

            'field_label_font_size' => 'labelSize',
            'field_label_margin' => 'labelMargin',
            'field_value_font_size' => 'fieldSize',
            'field_value_margin' => 'fieldMargin',

            'logo_max_width' => 'logoMaxWidth',
            'logo_max_height' => 'logoMaxHeight', // Tape only
            'logo_margin' => 'logoMargin',

            'text_area_width' => 'textAreaWidth',
            'text_area_height' => 'textAreaHeight',
            'text_size_mod' => 'textSizeMod', // Tape only
            'text_area_offset_y' => 'textAreaOffsetY', // Tape only
        ];
    }

    protected function editorStringContentMap(): array
    {
        return [
            'barcode2D_h_align' => 'barcode2DHAlign',
            'barcode2D_v_align' => 'barcode2DVAlign',

            'barcode1D_v_align' => 'barcode1DVAlign',
            'barcode1D_placement' => 'barcode1DPlacement', // Tape only
            'barcode2D_placement' => 'barcode2DPlacement', // Tape only

            'tag_alignment' => 'tagAlignment',
            'tag_position_mode' => 'tagPositionMode', // Tape only
            'tag_font' => 'tagFont',

            'title_font' => 'titleFont',
            'title_position' => 'titlePosition',

            'field_alignment' => 'fieldAlignment', // Tape only
            'field_label_font' => 'fieldLabelFont',
            'field_value_font' => 'fieldValueFont',

            'logo_h_align' => 'logoHAlign',
            'logo_v_align' => 'logoVAlign',
            'logo_placement' => 'logoPlacement', // Tape only

            'text_render_mode' => 'textRenderMode', // Tape only

        ];
    }
}