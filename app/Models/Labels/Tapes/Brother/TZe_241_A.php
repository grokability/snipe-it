<?php

namespace App\Models\Labels\Tapes\Brother;

use App\Helpers\Helper;

class TZe_241_A extends TZe_18mm
{
    private const LABEL_SIZE = 5.0;

    private const LABEL_MARGIN = 0.6;

    private const FIELD_SIZE = 5.0;

    private const FIELD_MARGIN = 0.8;

    public function getUnit()
    {
        return 'mm';
    }

    public function getWidth()
    {
        return 50.0;
    }

    public function getSupportAssetTag()
    {
        return false;
    }

    public function getSupport1DBarcode()
    {
        return false;
    }

    public function getSupport2DBarcode()
    {
        return false;
    }

    public function getSupportFields()
    {
        return 2;
    }

    public function getSupportLogo()
    {
        return false;
    }

    public function getSupportTitle()
    {
        return false;
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

    protected function getContentEditorConfig(): array
    {
        return [
            'field_label_font_size' => 4,
            'field_label_margin' => $this->getLabelMargin(),

            'field_value_font_size' => 4,
            'field_value_margin' => -2.2,

            'text_render_mode' => 'block',
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;

        $fields = $record->get('fields') ?? [];

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
            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', '', $field_layout['labelSize'], 'L',
                $field_layout['labelWidth'], $field_layout['rowAdvance'], true, 0
            );

            static::writeText(
                $pdf, $field['value'],
                $field_layout['valueX'], $currentY,
                'freemono', 'B', $field_layout['fieldSize'], 'L',
                $field_layout['valueWidth'], $field_layout['rowAdvance'], true, 0, 0.01
            );
            $currentY += $field_layout['rowAdvance'];
        }
    }
}
