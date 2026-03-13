<?php

namespace App\Models\Labels\Sheets\Avery;


class L7162_B extends L7162
{
    private const BARCODE_SIZE   =   6.00;
    private const BARCODE_MARGIN =   1.40;
    private const TAG_SIZE       =   3.20;
    private const LOGO_MAX_WIDTH =  25.00;
    private const LOGO_MARGIN    =   2.20;
    private const TITLE_SIZE     =   4.20;
    private const TITLE_MARGIN   =   1.20;
    private const LABEL_SIZE     =   2.20;
    private const LABEL_MARGIN   = - 0.50;
    private const FIELD_SIZE     =   4.20;
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
        return 0; 
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
        return true; 
    }
    public function getSupport2DBarcode()
    {
        return false; 
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
        $pa = $this->getLabelPrintableArea();

        $usableWidth = $pa->w;
        $usableHeight = $pa->h;
        $currentX = $pa->x1;
        $currentY = $pa->y1;

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $pa->x1, $pa->y2 - self::BARCODE_SIZE, 
                $usableWidth, self::BARCODE_SIZE
            );
            $usableHeight -= self::BARCODE_SIZE + self::BARCODE_MARGIN;
        }

        if ($record->has('logo')) {
            $logoSize = static::writeImage(
                $pdf, $record->get('logo'),
                $pa->x1, $pa->y1,
                self::LOGO_MAX_WIDTH, $usableHeight,
                'L', 'T', 300, true, false, 0
            );
            $currentX += $logoSize[0] + self::LOGO_MARGIN;
            $usableWidth -= $logoSize[0] + self::LOGO_MARGIN;
        }

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
                $usableWidth, self::LABEL_SIZE, true, 0
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

        static::writeText(
            $pdf, $record->get('tag'),
            $currentX, $pa->y2 - self::BARCODE_SIZE - self::BARCODE_MARGIN - self::TAG_SIZE,
            'freemono', 'b', self::TAG_SIZE, 'R',
            $usableWidth, self::TAG_SIZE, true, 0, 0.3
        );

    }
}


?>
