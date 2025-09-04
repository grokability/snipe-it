<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_18mm_A extends TZe_18mm
{
    private const BARCODE_SIZE   = 3.20;
    private const BARCODE_MARGIN = 0.30;
    private const TEXT_SIZE_MOD  = 1.00;

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
        return true; 
    }
    public function getSupport1DBarcode()
    {
        return true; 
    }
    public function getSupport2DBarcode()
    {
        return false; 
    }
    public function getSupportFields()
    {
        return 1; 
    }
    public function getSupportLogo()
    {
        return false; 
    }
    public function getSupportTitle()
    {
        return false; 
    }

    public function preparePDF($pdf)
    {
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $pa->x1, $pa->y1, $pa->w, self::BARCODE_SIZE
            );
        }

        $currentY = $pa->y1 + self::BARCODE_SIZE + self::BARCODE_MARGIN;
        $usableHeight = $pa->h - self::BARCODE_SIZE - self::BARCODE_MARGIN;
        $fontSize = $usableHeight + self::TEXT_SIZE_MOD;

        $tagWidth = $pa->w / 3;
        $fieldWidth = $pa->w / 3 * 2;

        static::writeText(
            $pdf, $record->get('tag'),
            $pa->x1, $currentY,
            'freemono', 'b', $fontSize, 'L',
            $tagWidth, $usableHeight, true, 0, 0
        );

        if ($record->get('fields')->count() >= 1) {
            static::writeText(
                $pdf, $record->get('fields')->values()->get(0)['value'],
                $pa->x1 + ($tagWidth), $currentY,
                'freemono', 'b', $fontSize, 'R',
                $fieldWidth, $usableHeight, true, 0, 0
            );
        }

    }
}