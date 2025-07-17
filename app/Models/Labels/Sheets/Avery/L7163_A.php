<?php

namespace App\Models\Labels\Sheets\Avery;


class L7163_A extends L7163
{
    private const BARCODE_MARGIN =   1.80;
    private const TAG_SIZE       =   4.80;
    private const TITLE_SIZE     =   5.00;
    private const TITLE_MARGIN   =   1.80;
    private const LABEL_SIZE     =   2.35;
    private const LABEL_MARGIN   = - 0.30;
    private const FIELD_SIZE     =   4.80;
    private const FIELD_MARGIN   =   0.30;

    public function getUnit()
    {
        return 'mm'; 
    }

    public function getLabelMarginTop()
    {
        return 1.0; 
    }
    public function getLabelMarginBottom()
    {
        return 1.0; 
    }
    public function getLabelMarginLeft()
    {
        return 1.0; 
    }
    public function getLabelMarginRight()
    {
        return 1.0; 
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

        $usableWidth = $pa->w;
        $usableHeight = $pa->h;
        $currentX = $pa->x1;
        $currentY = $pa->y1;

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', '', self::TITLE_SIZE, 'C',
                $usableWidth, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        $barcodeSize = $pa->h - self::TITLE_SIZE - self::TITLE_MARGIN - self::TAG_SIZE;
        
        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freemono', 'b', self::TAG_SIZE, 'C',
                $barcodeSize, self::TAG_SIZE, true, 0
            );
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $pa->x1, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= $barcodeSize + self::BARCODE_MARGIN;
        } else {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freemono', 'b', self::TAG_SIZE, 'R',
                $usableWidth, self::TAG_SIZE, true, 0
            );
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
                $usableWidth, self::FIELD_SIZE, true, 0, 0.5
            );
            $currentY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }

    }
}


?>