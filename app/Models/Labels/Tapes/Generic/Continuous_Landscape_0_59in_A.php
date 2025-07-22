<?php

namespace App\Models\Labels\Tapes\Generic;

class Continuous_Landscape_0_59in_A extends Continuous_Landscape_0_59in
{
    public function getUnit()
    {
        return 'in'; 
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

    public function preparePDF($pdf)
    {
        $pdf->SetAutoPageBreak(false);
    }

    public function write($pdf, $record, $calculateOnly = false)
    {
        $pa = $this->getPrintableArea();
        
        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;
        
        // Calculate required length based on content
        $requiredLength = 0;
        
        // Use full usable height for barcode
        $barcodeSize = $usableHeight;
        
        // Add barcode width to required length
        if ($record->has('barcode2d') && $this->getSupport2DBarcode()) {
            $requiredLength += $barcodeSize;
            // Add gap between barcode and fields
            $requiredLength += $this->barcodeMargin;
        }
        
        // Calculate fields width using accurate text measurement
        if ($record->has('fields') && $this->getSupportFields() > 0) {
            $fields = array_slice($record->get('fields')->toArray(), 0, $this->getSupportFields());
            $fieldsWidth = 0;
            
            foreach ($fields as $field) {
                $labelText = $field['label'] ?? '';
                $valueText = $field['value'] ?? '';
                
                // Calculate accurate width using the PDF object
                $labelWidth = $this->calculateTextWidth($pdf, $labelText, 'freesans', 'B', $this->labelSize * 1.2);
                $valueWidth = $this->calculateTextWidth($pdf, $valueText, 'freemono', 'B', $this->fieldSize * 1.3);
                
                // Use the longer of the two
                $textWidth = max($labelWidth, $valueWidth);
                $fieldsWidth = max($fieldsWidth, $textWidth);
            }
            
            $requiredLength += $fieldsWidth;
        }
        
        // Add more padding to prevent text from being cut off
        // $requiredLength += self::TAPE_WIDTH * 0.8;
        
        // Ensure minimum length
        $requiredLength = max($this->minHeight, $requiredLength);
        
        // If we're just calculating, return the length
        if ($calculateOnly) {
            return $requiredLength;
        }
        
        // Otherwise, render the content
        // Position barcode on the left side
        if ($record->has('barcode2d') && $this->getSupport2DBarcode()) {
            // Position at top of usable area
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + $this->barcodeMargin;
            $usableWidth -= $barcodeSize + $this->barcodeMargin;
        }
        
        // Position fields to the right of the barcode
        if ($record->has('fields') && $this->getSupportFields() > 0) {
            // Limit to the number of supported fields
            $fields = array_slice($record->get('fields')->toArray(), 0, $this->getSupportFields());
            
            // Calculate total height needed for fields
            $totalFieldsHeight = 0;
            foreach ($fields as $field) {
                $totalFieldsHeight += $this->labelSize * 1.2 + $this->labelMargin; // Increased label size by 20%
                $totalFieldsHeight += $this->fieldSize * 1.3 + $this->fieldMargin * 2; // Increased field size by 30% and margin
            }
            
            // Start position - respect top margin
            $fieldY = $currentY; // $currentY already includes the top margin
            $fieldWidth = $usableWidth;
            
            // Calculate available height for fields (respecting margins)
            $availableHeight = $usableHeight;
            
            // If fields don't fill available height, adjust spacing proportionally
            // but don't exceed the available height
            $scaleFactor = 1.0; // Default scale factor
            if ($totalFieldsHeight < $availableHeight && count($fields) > 0) {
                // Scale up to fill available height, but not too much
                $scaleFactor = min(1.5, $availableHeight / $totalFieldsHeight);
            } else if ($totalFieldsHeight > $availableHeight && count($fields) > 0) {
                // Scale down to fit within available height
                $scaleFactor = $availableHeight / $totalFieldsHeight;
            }
            
            foreach ($fields as $field) {
                // Calculate scaled spacing
                $labelHeight = $this->labelSize * 1.2 * $scaleFactor;
                $labelSpacing = $this->labelMargin * $scaleFactor;
                $fieldHeight = $this->fieldSize * 1.3 * $scaleFactor;
                $fieldSpacing = $this->fieldMargin * 2 * $scaleFactor;
                
                // Check if label is empty or null
                $labelText = $field['label'] ?? '';
                $valueText = $field['value'] ?? '';
                
                if (empty(trim($labelText))) {
                    // If label is empty, just render the value at the current Y position
                    static::writeText(
                        $pdf, $valueText,
                        $currentX, $fieldY,
                        'freemono', 'B', $this->fieldSize * 1.3, 'L', // Increased field size by 30%
                        $fieldWidth, $fieldHeight, false, 0, 0.00
                    );
                    $fieldY += ($fieldHeight + $fieldSpacing) + 0.02; // Increased spacing after value
                } else {
                    // If label has content, render both label and value
                    static::writeText(
                        $pdf, $labelText,
                        $currentX, $fieldY,
                        'freesans', 'B', $this->labelSize * 1.2, 'L', // Increased label size by 20% and made bold
                        $labelWidth, $labelHeight, false, 0,
                    );
                    $fieldY += ($labelHeight + $labelSpacing) + 0.01;
                    
                    // Value
                    static::writeText(
                        $pdf, $valueText,
                        $currentX, $fieldY, // Position value directly below label
                        'freemono', 'B', $this->fieldSize * 1.3, 'L', // Increased field size by 30%
                        $fieldWidth, $fieldHeight, false, 0, 0.00
                    );
                    $fieldY += ($fieldHeight + $fieldSpacing) + 0.02; // Increased spacing after value
                }
            }
        }
    }
}