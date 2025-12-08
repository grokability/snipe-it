<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_241 extends TZe_18mm
{
    private const LABEL_SIZE   = 5.0;
    private const LABEL_MARGIN = 0.6;
    private const FIELD_SIZE   = 5.0;
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

    public function preparePDF($pdf){}

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX      = $pa->x1;
        $currentY      = $pa->y1;
        $usableWidth   = $pa->w;
        $usableHeight  = $pa->h;

        $fields = $record->get('fields') ?? [];

        $fieldCount = count($fields);

        $perFieldHeight = (self::LABEL_SIZE + self::LABEL_MARGIN)
            + (self::FIELD_SIZE + self::FIELD_MARGIN);

        $baseHeight = $fieldCount * $perFieldHeight;

        // If it doesn't fit in the available height, scale everything down
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