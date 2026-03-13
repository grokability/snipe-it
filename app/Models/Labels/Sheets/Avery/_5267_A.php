<?php

namespace App\Models\Labels\Sheets\Avery;


class _5267_A extends _5267
{
    private const BARCODE_SIZE   =   0.175;
    private const BARCODE_MARGIN =   0.000;
    private const TAG_SIZE       =   0.125;
    private const TITLE_SIZE     =   0.140;
    private const FIELD_SIZE     =   0.150;
    private const FIELD_MARGIN   =   0.012;

    public function getUnit()
    {
        return 'in'; 
    }

    public function getLabelMarginTop()
    {
        return 0.02; 
    }
    public function getLabelMarginBottom()
    {
        return 0.00; 
    }
    public function getLabelMarginLeft()
    {
        return 0.04; 
    }
    public function getLabelMarginRight()
    {
        return 0.04; 
    }

    public function getSupportAssetTag()
    {
        return false; 
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
        return true; 
    }

    public function preparePDF($pdf)
    {
    }

    public function write($pdf, $record)
    {
        $pa = $this->getLabelPrintableArea();

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $pa->x1, $pa->y2 - self::BARCODE_SIZE, 
                $pa->w, self::BARCODE_SIZE
            );
        }

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $pa->x1, $pa->y1,
                'freesans', '', self::TITLE_SIZE, 'L',
                $pa->w, self::TITLE_SIZE, true, 0
            );
        }

        $fieldY = $pa->y2 - self::BARCODE_SIZE - self::BARCODE_MARGIN - self::FIELD_SIZE;
        if ($record->has('fields')) {
            if ($record->get('fields')->count() >= 1) {
                $field = $record->get('fields')->first();
                static::writeText(
                    $pdf, $field['value'],
                    $pa->x1, $fieldY,
                    'freemono', 'B', self::FIELD_SIZE, 'C',
                    $pa->w, self::FIELD_SIZE, true, 0, 0.01
                );
            }
        }

    }
}


?>