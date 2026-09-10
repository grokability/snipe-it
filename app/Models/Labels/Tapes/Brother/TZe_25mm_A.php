<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_25mm_A extends TZe_25mm
{
    private const BARCODE_MARGIN = 1.00;

    private const BARCODE2D_SIZE = 19.00;

    private const BARCODE2D_MARGIN = 1.00;

    private const TAG_SIZE = 0.00;

    private const LOGO_MAX_WIDTH = 10.00;

    private const LOGO_MARGIN = 2.20;

    private const TITLE_SIZE = 2.80;

    private const TITLE_MARGIN = 0.50;

    private const LABEL_SIZE = 2.00;

    private const LABEL_MARGIN = -0.35;

    private const FIELD_SIZE = 3.50;

    private const FIELD_MARGIN = 0.15;

    public function getUnit()
    {
        return 'mm';
    }

    public function getWidth()
    {
        return 24.0;
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

    public function preparePDF($pdf) {}

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;
        //$barcodeSize = self::BARCODE2D_SIZE;
        $barcodeSize = $pa->w - self::TAG_SIZE;

        if ($record->has('barcode2d')) {
            //static::writeText(
            //    $pdf, $record->get('tag'),
            //    $pa->x1, $pa->y2 - self::TAG_SIZE,
            //    'freemono', 'b', self::TAG_SIZE, 'C',
            //    $barcodeSize, self::TAG_SIZE, true, 0
            //);
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= ($barcodeSize + self::BARCODE_MARGIN);
        }
      
        //$textY = $currentY + ($barcodeSize / 2) - (self::FIELD_SIZE / 2);
        //foreach ($record->get('fields') as $field) {
        //    static::writeText(
        //        $pdf, $field['value'],
        //         $pa->x1, $pa->y2 - self::FIELD_SIZE,
        //        'freemono', 'B', self::FIELD_SIZE, 'R',
        //          $usableWidth, self::FIELD_SIZE, true, 0, 0.3
        //    );
        //}

        //} else {
        // === BELOW barcode ===

        //$textY = $currentX + $barcodeSize + self::FIELD_MARGIN;
	foreach ($record->get('fields') as $field) {
		$value = $field['value'] ?? '';
            	$lines = str_split($value, 10);
                foreach ($lines as $line) {
                    static::writeText(
                    $pdf, $line,
                    $pa->x1, $pa->y2 - 12,
                    'freemono','B', self::FIELD_SIZE,'L',
                    $usableWidth, self::FIELD_SIZE, false, 0, 0
                    );
                    $pa->y2 += 2.30 + self::FIELD_MARGIN;
                }
        }
    }
}

