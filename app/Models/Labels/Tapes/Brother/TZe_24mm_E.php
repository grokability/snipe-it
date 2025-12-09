<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_24mm_E extends TZe_24mm
{
    private const BARCODE_MARGIN =   1.75;
    private const TAG_SIZE       =   2.00;
    private const TITLE_SIZE     =   2.80;
    private const TITLE_MARGIN   =   0.50;
    private const LABEL_SIZE     =   2.00;
    private const LABEL_MARGIN   = - 0.75;
    private const FIELD_SIZE     =   2.80;
    private const FIELD_MARGIN   =   0.15;
    private const BARCODE1D_SIZE = - 2.25;

    public function getUnit()  { return 'mm'; }
    public function getWidth() { return 45.0; }
	public function getHeight() { return 15; }
    public function getSupportAssetTag()  { return true; }
    public function getSupport1DBarcode() { return true; }
    public function getSupport2DBarcode() { return true; }
    public function getSupportFields()    { return 3; }
    public function getSupportLogo()      { return false; }
    public function getSupportTitle()     { return true; }

    public function preparePDF($pdf) {}

    public function write($pdf, $record) {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1 -2;
        $usableWidth = $pa->w;


        $usableHeight = $pa->h - self::BARCODE1D_SIZE;
        $barcodeSize = ($usableHeight - self::TAG_SIZE) * 1.2;

        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE - self::BARCODE1D_SIZE,
                'freesans', 'b', self::TAG_SIZE, 'C',
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
                $pa->x1, $pa->y2 - self::TAG_SIZE - self::BARCODE1D_SIZE,
                'freesans', 'B', self::TAG_SIZE, 'R',
                $usableWidth, self::TAG_SIZE, true, 0
            );
        }

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', 'B', self::TITLE_SIZE, 'L',
                $usableWidth, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        $fields = $record->get('fields');
        // Figure out how tall the label fields wants to be
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
        $fieldSize   = self::FIELD_SIZE   * $scale;
        $fieldMargin = self::FIELD_MARGIN * $scale;

        foreach ($fields as $field) {
            // Write label and value on the same line
            // Calculate label width with proportional character spacing
            $labelWidth = $pdf->GetStringWidth($field['label'], 'freesans', '', $labelSize);
            $charCount = strlen($field['label']);
            $spacingPerChar = 0.5;
            $totalSpacing = $charCount * $spacingPerChar;
            $adjustedWidth = $labelWidth + $totalSpacing;

            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', 'B', $labelSize, 'L',
                $adjustedWidth, $labelSize, true, 0, $spacingPerChar
            );

            static::writeText(
                $pdf, $field['value'],
                $currentX + $adjustedWidth + 2, $currentY,
                'freesans', 'B', $fieldSize, 'L',
                $usableWidth - $adjustedWidth - 2, $fieldSize, true, 0, 0.3
            );

            $currentY += max($labelSize, $fieldSize) +$fieldMargin;
        }

        
        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $currentX, $barcodeSize + self::BARCODE_MARGIN, $usableWidth - self::TAG_SIZE, self::TAG_SIZE
            );
        }
    }
}
