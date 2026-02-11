<?php

namespace App\Models\Labels\Sheets\Avery;


use App\Helpers\Helper;

class L7163_A extends L7163
{
    private const BARCODE_MARGIN =   1.80;
    private const TAG_SIZE       =   4.80;
    private const TITLE_SIZE     =   4.00;
    private const TITLE_MARGIN   =   1.80;
    private const LABEL_SIZE     =   2.35;
    private const LABEL_MARGIN   = - 0.30;
    private const FIELD_SIZE     =   3.80;
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



        $barcodeSize = $pa->h - self::TITLE_SIZE - self::TITLE_MARGIN - self::TAG_SIZE;

        if ($record->has('barcode2d')) {
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $pa->x1, $currentY,
                $barcodeSize, $barcodeSize
            );

            $tagGap = 0.6;
            $tagY   = $currentY + $barcodeSize + $tagGap;
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $tagY,
                'freemono', 'b', self::TAG_SIZE, 'C',
                $barcodeSize, self::TAG_SIZE, true, 0
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
        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', '', self::TITLE_SIZE, 'C',
                $usableWidth, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }
        $fields = $record->get('fields');
        $labelLineH = max(2.0, self::LABEL_SIZE * 0.9);  // mm-ish line height
        $valueLineH = max(3.5, self::FIELD_SIZE * 1.05);
        $pairGap    = 0.6;

        foreach ($fields as $field) {
            $rawLabel = trim((string)($field['label'] ?? ''));
            $value    = trim((string)($field['value'] ?? ''));

            if ($rawLabel === '' && $value === '') {
                continue;
            }

            // LABEL (small, above)
            if ($rawLabel !== '') {
                $labelText = rtrim($rawLabel, ':'); // in your screenshot there’s no colon

                static::writeText(
                    $pdf, $labelText,
                    $currentX, $currentY,
                    $this->getLabelFont(), '', self::LABEL_SIZE, 'L',
                    $usableWidth, $labelLineH, true, 0
                );

                $currentY += $labelLineH;
            }

            // VALUE (big, below)
            if ($value !== '') {
                static::writeText(
                    $pdf, $value,
                    $currentX, $currentY,
                    $this->getLabelValueFont(), 'B', self::FIELD_SIZE, 'L',
                    $usableWidth, $valueLineH, true, 0, 0.01
                );

                $currentY += $valueLineH;
            }

            // gap before next pair
            $currentY += $pairGap;
        }

    }
}


?>