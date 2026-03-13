<?php

namespace App\Models\Labels\Tapes\Generic;

use App\Helpers\Helper;

abstract class Continuous_Landscape_0_59in extends GenericTape
{
    // abstract class for printers using 0.59in width paper in landscape orientation
    // Using a larger TAPE_WIDTH value to increase font sizes
    protected const TAPE_WIDTH = 0.59;
    private float $tapeHeight;
    protected float $minHeight = 1;

    /**
     * @param float $length     Length of the label in inches (default 2.36in which is 60mm)
     * @param bool  $continuous Whether the tape is continuous or pre-cut
     * @param float $spacing    Spacing between labels for non-continuous tapes (in inches)
     */
    public function __construct($length = 0.6, $continuous = true, $spacing = 0.0)
    {
        // Swap width and height for landscape orientation
        // The height becomes the width, and the length becomes the height
        parent::__construct($length, self::TAPE_WIDTH, $continuous, $spacing);
        $this->tapeHeight = $length;
        
        $this->marginTop = 0.1;
        $this->marginBottom = 0.1;
        // Keep small horizontal margins
        $this->marginLeft = self::TAPE_WIDTH * 0.2;
        // $this->marginRight = self::TAPE_WIDTH * 0.1;
        
        // Override font sizes to make them larger
        // Calculate a larger base font size (3x the default)
        $baseFontSize = self::TAPE_WIDTH * 0.16; // 3x the default 0.07
        
        // Recalculate all element sizing based on the larger base font size
        $this->titleSize = $baseFontSize;                // Same as base font size
        $this->titleMargin = $baseFontSize * 0.3;        // 30% of base font size
        $this->fieldSize = $baseFontSize * 1.1;          // 110% of base font size
        $this->fieldMargin = $baseFontSize * 0.1;        // 10% of base font size
        $this->labelSize = $baseFontSize * 0.7;          // 70% of base font size
        $this->labelMargin = $baseFontSize * -0.1;       // -10% of base font size
        $this->barcodeMargin = $baseFontSize * 0.9;      // 20% of base font size
        $this->tagSize = $baseFontSize * 0.8;            // 80% of base font size
    }
    
    public function getBarcodeRatio()
    {
        return 1.0;  // Barcode should use 100% of available height
    }
    
    /**
     * Calculate the required length for the content
     *
     * @param  $record The record to calculate length for
     * @return float The calculated length in inches
     */
    protected function calculateRequiredLength($record)
    {

        // Calculate length needed for barcode and fields side by side
        $requiredLength = 0;
        
        // Add barcode length if present
        if (($record->has('barcode2d') && $this->getSupport2DBarcode()) 
            || ($record->has('barcode') && $this->getSupport1DBarcode())
        ) {
            // Use full tape width for barcode size
            $barcodeSize = self::TAPE_WIDTH;
            $requiredLength += $barcodeSize + $this->barcodeMargin * 0.3; // Minimal margin
        }
        
        // Add fields length if present - calculate based on actual content
        if ($record->has('fields') && $this->getSupportFields() > 0) {
            $fields = array_slice($record->get('fields')->toArray(), 0, $this->getSupportFields());
            
            // Base width for field area
            $fieldsWidth = self::TAPE_WIDTH;
            
            // Calculate additional width based on text length
            foreach ($fields as $field) {
                // Get label and value text
                $labelText = $field['label'] ?? '';
                $valueText = $field['value'] ?? '';
                
                // Calculate approximate width needed based on text length
                // Increase character width to ensure enough space (0.15 inches per character)
                $labelWidth = strlen($labelText) * 0.09;
                $valueWidth = strlen($valueText) * 0.09;
                
                // Use the longer of the two
                $textWidth = max($labelWidth, $valueWidth);
                
                // Ensure minimum width and add to total
                $fieldsWidth = max($fieldsWidth, $textWidth);
            }
            
            // Add the calculated width for fields
            $requiredLength += $fieldsWidth;
            
            // Add minimal extra space for field padding
            // Reduce padding to eliminate extraneous space on right edge
            // $requiredLength += self::TAPE_WIDTH * 0.1;
        }
        
        // Ensure minimum length
        return max($this->minHeight, $requiredLength);
    }
    
    /**
     * Calculate text width accurately using the PDF object
     *
     * @param  $pdf   The PDF object
     * @param  string $text  The text to measure
     * @param  string $font  The font to use
     * @param  string $style The font style
     * @param  float  $size  The font size
     * @return float The calculated width
     */
    protected function calculateTextWidth($pdf, $text, $font, $style, $size)
    {
        $originalFont = $pdf->getFontFamily();
        $originalStyle = $pdf->getFontStyle();
        $originalSize = $pdf->getFontSizePt();
        
        $pdf->SetFont($font, $style, Helper::convertUnit($size, $this->getUnit(), 'pt', true));
        $width = $pdf->GetStringWidth($text);
        
        // Restore original font settings
        $pdf->SetFont($originalFont, $originalStyle, $originalSize);
        
        return $width;
    }
    
    /**
     * Override the writeAll method to support dynamic page sizes for continuous tapes
     */
    public function writeAll($pdf, $data)
    {
        // Use auto-sizing for continuous tapes, fixed height for die-cut tapes
        if ($this->continuous) {
            $data->each(
                function ($record, $index) use ($pdf) {
                    // Calculate the required length by calling write with calculateOnly=true
                    $requiredLength = $this->write($pdf, $record);
                
                    // If write didn't return a length (old implementation), fall back to calculateRequiredLength
                    if ($requiredLength === null) {
                        $requiredLength = $this->calculateRequiredLength($record);
                    }
                
                    // Temporarily update the height property
                    $originalHeight = $this->height;
                    $this->height = self::TAPE_WIDTH; // Keep height fixed at tape width
                
                    // Add a new page with the calculated dimensions
                    // Keep height fixed at TAPE_WIDTH, use calculated length for width
                    $pdf->AddPage(
                        $this->getOrientation(),
                        [$requiredLength, self::TAPE_WIDTH],
                        false, // Don't reset page number
                        false  // Don't reset object ID
                    );
                
                    // Write the content
                    $this->write($pdf, $record);
                
                    // Restore the original height
                    $this->height = $originalHeight;
                }
            );
        } else {
            // Use the default implementation for non-continuous (die-cut) tapes
            parent::writeAll($pdf, $data);
        }
    }
}