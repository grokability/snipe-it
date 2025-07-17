<?php

namespace App\Models\Labels\Tapes\Generic;

class Continuous_53mm_A extends Continuous_53mm
{
    
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
        return true; 
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
    
    public function preparePDF($pdf)
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