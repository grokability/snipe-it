<?php

namespace App\Models\Labels\Sheets\Avery;


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
        $fieldCount = count($fields);

        $perFieldHeight = (self::LABEL_SIZE + self::LABEL_MARGIN)
                       + (self::FIELD_SIZE + self::FIELD_MARGIN);

        $baseHeight = $fieldCount * $perFieldHeight;
        $scale = 1.0;
        if ($baseHeight > $usableHeight && $baseHeight > 0) {
            $scale = $usableHeight / $baseHeight;
        }

        $labelSize   = self::LABEL_SIZE   * $scale;
        $labelMargin = self::LABEL_MARGIN * $scale;
        $fieldSize   = self::FIELD_SIZE   * $scale;
        $fieldMargin = self::FIELD_MARGIN * $scale;

        foreach ($fields as $field) {
            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', '', $labelSize, 'L',
                $usableWidth, $labelSize, true, 0
            );
            $currentY += $labelSize + $labelMargin;

            static::writeText(
                $pdf, $field['value'],
                $currentX, $currentY,
                'freemono', 'B', $fieldSize, 'L',
                $usableWidth, $fieldSize, true, 0, 0.01
            );
            $currentY += $fieldSize + $fieldMargin;
        }

    }
}


?>
