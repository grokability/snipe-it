<?php

namespace App\Models\Labels\CustomLabels\Concerns;

trait HasCustomLabelEditorConfig
{
    protected function getContentEditorConfig(): array
    {
        return [
            // Barcode 1D
            'barcode_size' => $this->getBarcodeSize(),
            'barcode_margin' => $this->getBarcodeMargin(),

            // Barcode 2D
            'barcode_2d_size' => $this->get2DBarcodeSize(),
            'barcode2D_h_align' => $this->getBarcode2DHAlign(),
            'barcode2D_v_align' => $this->getBarcode2DVAlign(),

            // Tag
            'tag_font' => $this->getTagFont(),
            'tag_font_size' => $this->getTagSize(),
            'tag_alignment' => $this->getTagAlignment(),
            'tag_offset_x' => $this->getTagOffsetX(),
            'tag_offset_y' => $this->getTagOffsetY(),

            // Title
            'title_font' => $this->getTitleFont(),
            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),
            'title_offset_x' => $this->getTitleOffsetX(),
            'title_position' => $this->getTitlePosition(),

            // Fields
            'field_label_font' => $this->getFieldLabelFont(),
            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),

            'field_value_font' => $this->getFieldValueFont(),
            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),

            // Logo
            'logo_max_width' => $this->getLogoMaxWidth(),
            'logo_margin' => $this->getLogoMargin(),
            'logo_h_align' => $this->getLogoHAlign(),
            'logo_v_align' => $this->getLogoVAlign(),

            // Text Area
            'text_area_width' => $this->getTextAreaWidth(),
            'text_area_height' => $this->getTextAreaHeight(),
        ];
    }
}