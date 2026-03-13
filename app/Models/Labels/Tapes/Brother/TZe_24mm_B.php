<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_24mm_B extends TZe_24mm
{
    private const BARCODE_MARGIN =   1.40;
    private const TAG_SIZE       =   2.80;
    private const LOGO_MAX_WIDTH =  20.00;
    private const LOGO_MARGIN    =   2.20;
    private const TITLE_SIZE     =   2.80;
    private const TITLE_MARGIN   =   0.50;
    private const LABEL_SIZE     =   2.00;
    private const LABEL_MARGIN   = - 0.35;
    private const FIELD_SIZE     =   3.20;
    private const FIELD_MARGIN   =   0.15;

    public function getUnit()
    {
        return 'mm'; 
    }
    public function getWidth()
    {
        return 73.0; 
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
        return 3; 
    }
    public function getSupportLogo()
    {
        return true; 
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
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;

        $barcodeSize = $pa->h - self::TAG_SIZE;

        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freemono', 'b', self::TAG_SIZE, 'C',
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
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freemono', 'b', self::TAG_SIZE, 'R',
                $usableWidth, self::TAG_SIZE, true, 0
            );
        }

        $usableWidth -= self::LOGO_MAX_WIDTH - self::LOGO_MARGIN;

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', '', self::TITLE_SIZE, 'L',
                $usableWidth, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        foreach ($record->get('fields') as $field) {
            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', '', self::LABEL_SIZE, 'L',
                $usableWidth, self::LABEL_SIZE, true, 0, 0
            );
            $currentY += self::LABEL_SIZE + self::LABEL_MARGIN;

            static::writeText(
                $pdf, $field['value'],
                $currentX, $currentY,
                'freemono', 'B', self::FIELD_SIZE, 'L',
                $usableWidth, self::FIELD_SIZE, true, 0, 0.3
            );
            $currentY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }

         $currentX += $usableWidth + (self::LOGO_MARGIN/2);

        if ($record->has('logo')) {
            $logoSize = static::writeImage(
                $pdf, $record->get('logo'),
                $currentX, $pa->y1,
                self::LOGO_MAX_WIDTH, $usableHeight,
                'L', 'T', 300, true, false, 0
            );
            $currentX += $logoSize[0] + self::LOGO_MARGIN;
            $usableWidth -= $logoSize[0] + self::LOGO_MARGIN;
        }
    }
}
