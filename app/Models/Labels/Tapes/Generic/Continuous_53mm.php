<?php

namespace App\Models\Labels\Tapes\Generic;

abstract class Continuous_53mm extends GenericTape
{
    protected const TAPE_WIDTH = 53.0;
    
    // Minimum height for the label
    protected float $minHeight = 30.0;
    private float $tapeHeight;
    
    /**
     * Constructor for 53mm tape
     * 
     * Assumes tape is continuous, set to false and specify 
     * $spacing in concrete classfor die-cut labels
     *
     * @param float $height     Height of the label in mm (default 60mm)
     * @param bool  $continuous Whether the tape is continuous or pre-cut
     * @param float $spacing    Spacing between labels for non-continuous tapes (in mm)
     */
    public function __construct($height = 60.0, $continuous = true, $spacing = 0.0)
    {
        parent::__construct(self::TAPE_WIDTH, $height, $continuous, $spacing);
        $this->tapeHeight = $height;
    }
    
    public function getBarcodeRatio()
    {
        return 0.9;  // Barcode should use 90% of available width
    }
    
    /**
     * Calculate the required height for the content
     *
     * @param  $record The record to calculate height for
     * @return float The calculated height in mm
     */
    protected function calculateRequiredHeight($record)
    {
        $height = $this->marginTop + $this->marginBottom;
        
        // Add title height if present
        if ($record->has('title') && $this->getSupportTitle()) {
            $height += $this->titleSize + $this->titleMargin;
        }
        
        // Add barcode height if present
        if (($record->has('barcode2d') && $this->getSupport2DBarcode()) 
            || ($record->has('barcode') && $this->getSupport1DBarcode())
        ) {
            $pa = $this->getPrintableArea();
            $usableWidth = $pa->w;
            $barcodeSize = $usableWidth * $this->getBarcodeRatio();
            $height += $barcodeSize + $this->barcodeMargin;
        }
        
        // Add fields height if present
        if ($record->has('fields') && $this->getSupportFields() > 0) {
            foreach ($record->get('fields') as $field) {
                $height += $this->labelSize + $this->labelMargin;
                $height += $this->fieldSize + $this->fieldMargin;
            }
        }
        
        // Add a small buffer to ensure everything fits
        $height += 2.0;
        
        // Ensure minimum height
        return max($this->minHeight, $height);
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
                    // Calculate the required height for this record
                    $requiredHeight = $this->calculateRequiredHeight($record);
                
                    // Temporarily update the height property
                    $originalHeight = $this->height;
                    $this->height = $requiredHeight;
                
                    // Add a new page with the calculated dimensions
                    $pdf->AddPage(
                        $this->getOrientation(),
                        [$this->getWidth(), $requiredHeight],
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