<?php

namespace App\Models\Labels\Tapes\Generic;

use TCPDF;

class Tape_53mm_A extends Tape_53mm
{
    public function __construct()
    {
        parent::__construct(40.0, true, 0.0);
    }

    public function getUnit()
    {
        return 'mm';
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
        return 5;
    }

    public function getSupportLogo()
    {
        return false;
    }

    public function getSupportTitle()
    {
        return true;
    }

    public function getBarcodeMargin(): float
    {
        return $this->barcodeMargin;
    }

    public function getTitleSize(): float
    {
        return $this->titleSize;
    }

    public function getTitleMargin(): float
    {
        return $this->titleMargin;
    }

    public function getLabelSize(): float
    {
        return $this->labelSize;
    }

    public function getLabelMargin(): float
    {
        return $this->labelMargin;
    }

    public function getFieldSize(): float
    {
        return $this->fieldSize;
    }

    public function getFieldMargin(): float
    {
        return $this->fieldMargin;
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_2d_size' => 27.0,
            'barcode2D_v_align' => 'T',
            'barcode2D_placement' => 'stacked',

            'barcode_margin' => $this->getBarcodeMargin(),

            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),
            'title_offset_x' => '7',

            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),

            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),

            'text_render_mode' => 'vertical_stack',
        ];
    }
    public function preparePDF(TCPDF $pdf): void
    {
        $pdf->SetAutoPageBreak(false);
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $pa->x1, $pa->y1,
                'freesans', '', $this->titleSize, 'C',
                $pa->w, $this->titleSize, true, 0
            );
            $currentY += $this->titleSize + $this->titleMargin;
            $usableHeight -= $this->titleSize + $this->titleMargin;
        }

        // Make the barcode as large as possible while still leaving room for fields
        $barcodeSize = min($usableHeight * 0.8, $usableWidth * $this->getBarcodeRatio());

        if ($record->has('barcode2d')) {
            $barcodeX = $pa->x1 + ($usableWidth - $barcodeSize) / 2;

            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $barcodeX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentY += $barcodeSize + $this->barcodeMargin;
        }

        if ($record->has('fields')) {
            foreach ($record->get('fields') as $field) {
                static::writeText(
                    $pdf, $field['label'],
                    $currentX, $currentY,
                    'freesans', '', $this->labelSize, 'L',
                    $usableWidth, $this->labelSize, true, 0
                );
                $currentY += $this->labelSize + $this->labelMargin;

                static::writeText(
                    $pdf, $field['value'],
                    $currentX, $currentY,
                    'freemono', 'B', $this->fieldSize, 'L',
                    $usableWidth, $this->fieldSize, true, 0, 0.01
                );
                $currentY += $this->fieldSize + $this->fieldMargin;
            }
        }
    }
}
