<?php

namespace App\Models\Labels\Tapes\Brother;

use App\Helpers\Helper;

class TZe_24mm_E extends TZe_24mm
{
    private const BARCODE_MARGIN = 1.75;

    private const TAG_SIZE = 2.00;

    private const TITLE_SIZE = 2.80;

    private const TITLE_MARGIN = 0.50;

    private const LABEL_SIZE = 2.00;

    private const LABEL_MARGIN = -0.75;

    private const FIELD_SIZE = 2.80;

    private const FIELD_MARGIN = 0.15;

    private const BARCODE1D_SIZE = -2.25;

    public function getBarcodeMargin(): float
    {
        return self::BARCODE_MARGIN;
    }

    public function getTagSize(): float
    {
        return self::TAG_SIZE;
    }

    public function getTitleSize(): float
    {
        return self::TITLE_SIZE;
    }

    public function getTitleMargin(): float
    {
        return self::TITLE_MARGIN;
    }

    public function getLabelSize(): float
    {
        return self::LABEL_SIZE;
    }

    public function getLabelMargin(): float
    {
        return self::LABEL_MARGIN;
    }

    public function getFieldSize(): float
    {
        return self::FIELD_SIZE;
    }

    public function getFieldMargin(): float
    {
        return self::FIELD_MARGIN;
    }

    public function getUnit()
    {
        return 'mm';
    }

    public function getWidth()
    {
        return 45.0;
    }

    public function getHeight()
    {
        return 15;
    }

    public function getSupportAssetTag()
    {
        return true;
    }

    public function getSupport1DBarcode()
    {
        return true;
    }

    public function getSupport2DBarcode()
    {
        return true;
    }

    public function getSupportFields()
    {
        return 3;
    }

    public function getSupportLogo()
    {
        return false;
    }

    public function getSupportTitle()
    {
        return true;
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_margin' => $this->getBarcodeMargin(),
            'barcode1D_placement' => 'text_column',
            'barcode_size' => 2,
            'barcode2D_h_align' => 'L',
            'barcode2D_v_align' => 'T',

            'tag_font_size' => $this->getTagSize(),

            'title_font_size' => $this->getTitleSize(),
            'title_margin' => .5,
            'title_font' => 'freesans',

            'field_label_font_size' => 2,
            'field_label_margin' => $this->getLabelMargin(),
            'field_label_font' => 'freesans',

            'field_value_font_size' => 1.8,
            'field_value_margin' => -1.85,
            'field_value_font' => 'freemono',

            'tag_offset_y' => 2.5,
            'tag_alignment' => 'L',
            'tag_position_mode' => 'under_barcode',
            'tag_font' => 'freesans',
            'logo_h_align' => 'R',
            'text_render_mode' => 'block',
            'text_area_offset_y' => 2.0,
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1 - 2;
        $usableWidth = $pa->w;

        $usableHeight = $pa->h - self::BARCODE1D_SIZE;
        $barcodeSize = ($usableHeight - self::TAG_SIZE) * 1.2;

        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE - self::BARCODE1D_SIZE,
                'freesans', 'b', self::TAG_SIZE, 'C',
                $barcodeSize, self::TAG_SIZE, true, 0
            );
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= $barcodeSize + self::BARCODE_MARGIN;
        } else {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE - self::BARCODE1D_SIZE,
                'freesans', 'B', self::TAG_SIZE, 'R',
                $usableWidth, self::TAG_SIZE, true, 0
            );
        }

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', 'B', self::TITLE_SIZE, 'L',
                $usableWidth, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        $fields = $record->get('fields');

        $field_layout = Helper::labelFieldLayoutScaling(
            pdf: $pdf,
            fields: $fields,
            currentX: $currentX,
            usableWidth: $usableWidth,
            usableHeight: $usableHeight,
            baseLabelSize: self::LABEL_SIZE,
            baseFieldSize: self::FIELD_SIZE,
            baseFieldMargin: self::FIELD_MARGIN,
            baseLabelPadding: 1.5,
            baseGap: 1.5,
            maxScale: 1.8,
            labelFont: 'freesans',
        );

        foreach ($fields as $field) {
            $hasLabel = is_string($field['label'] ?? null) && trim($field['label']) !== '';
            if ($hasLabel) {
                static::writeText(
                    $pdf, $field['label'],
                    $currentX, $currentY,
                    'freesans', '', $field_layout['labelSize'], 'L',
                    $field_layout['labelWidth'], $field_layout['rowAdvance'], true, 0
                );
            }

            static::writeText(
                $pdf, $field['value'],
                $hasLabel ? $field_layout['valueX'] : $field_layout['fullValueX'], $currentY,
                'freemono', 'B', $field_layout['fieldSize'], 'L',
                $hasLabel ? $field_layout['valueWidth'] : $field_layout['fullValueWidth'], $field_layout['rowAdvance'], true, 0, 0.01
            );
            $currentY += $field_layout['rowAdvance'];
        }

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $currentX, $barcodeSize + self::BARCODE_MARGIN, $usableWidth - self::TAG_SIZE, self::TAG_SIZE
            );
        }
    }
}
