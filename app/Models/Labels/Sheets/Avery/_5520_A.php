<?php

namespace App\Models\Labels\Sheets\Avery;


class _5520_A extends _5520
{
    private const BARCODE_MARGIN =   0.075;
    private const TAG_SIZE       =   0.125;
    private const TITLE_SIZE     =   0.140;
    private const TITLE_MARGIN   =   0.040;
    private const LABEL_SIZE     =   0.090;
    private const LABEL_MARGIN   =  -0.015;
    private const FIELD_SIZE     =   0.150;
    private const FIELD_MARGIN   =   0.012;

    public function getUnit()
    {
        return 'in'; 
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
        return false; 
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
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
            $usableHeight -= self::TITLE_SIZE + self::TITLE_MARGIN;
        }

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

        foreach ($record->get('fields') as $field) {
            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', '', self::LABEL_SIZE, 'L',
                $usableWidth, self::LABEL_SIZE, true, 0
            );
            $currentY += self::LABEL_SIZE + self::LABEL_MARGIN;

            static::writeText(
                $pdf, $field['value'],
                $currentX, $currentY,
                'freemono', 'B', self::FIELD_SIZE, 'L',
                $usableWidth, self::FIELD_SIZE, true, 0, 0.01
            );
            $currentY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }

    }
}


?>