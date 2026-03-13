<?php

namespace App\Models\Labels\Sheets\Avery;


use App\Helpers\Helper;

class L4736_A extends L4736
{
    private const BARCODE_MARGIN =   1.80;
    private const TAG_SIZE       =   4.80;
    private const TITLE_SIZE     =   3.00;
    private const TITLE_MARGIN   =   1.80;
    private const LABEL_SIZE     =   2.8;
    private const LABEL_MARGIN   = - 0.45;
    private const FIELD_SIZE     =   3.80;
    private const FIELD_MARGIN   =   0.20;

    public function getUnit()
    {
        return 'mm';
    }

    public function getLabelMarginTop()
    {
        return 0.06;
    }
    public function getLabelMarginBottom()
    {
        return 0.06;
    }
    public function getLabelMarginLeft()
    {
        return 0.06;
    }
    public function getLabelMarginRight()
    {
        return 0.06;
    }

    public function getSupportAssetTag()
    {
        return true;
    }
    public function getSupport1DBarcode()
    {
        return false;
    }
    public function getSupport2DBarcode()
    {
        return true;
    }
    public function getSupportFields()
    {
        return 4;
    }
    public function getSupportLogo()
    {
        return false;
    }
    public function getSupportTitle()
    {
        return true;
    }

    public function preparePDF($pdf)
    {
    }

    public function write($pdf, $record)
    {
        $pa = $this->getLabelPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $pa->x1, $pa->y1,
                'freesans', '', self::TITLE_SIZE, 'C',
                $pa->w, self::TITLE_SIZE, true, 0
            );

        }
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
            $usableHeight -= self::TITLE_SIZE + self::TITLE_MARGIN;
        $barcodeSize = $usableHeight;
        if ($record->has('barcode2d')) {
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= $barcodeSize + self::BARCODE_MARGIN;
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


?>
