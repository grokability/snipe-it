<?php

namespace App\Models\Labels\Tapes\Generic;

use App\Helpers\Helper;
use App\Models\Labels\Label;

abstract class GenericTape extends Label
{
    // Default tape width in mm
    protected const TAPE_WIDTH = 42.0;
    
    // Tape properties
    protected float $width;
    protected float $height;
    protected bool $continuous;
    protected float $spacing = 0.0; // Space between labels for non-continuous tapes
    
    // Margins in mm
    protected float $marginTop;
    protected float $marginBottom;
    protected float $marginLeft;
    protected float $marginRight;
    
    // Element sizing in mm
    protected float $titleSize;
    protected float $titleMargin;
    protected float $fieldSize;
    protected float $fieldMargin;
    protected float $labelSize;
    protected float $labelMargin;
    protected float $barcodeMargin;
    protected float $tagSize;
    
    /**
     * Constructor for generic tape
     * 
     * @param float $width      Width of the tape in mm
     * @param float $height     Height of the label in mm (for continuous tapes, this is the default height)
     * @param bool  $continuous Whether the tape is continuous or pre-cut
     * @param float $spacing    Spacing between labels for non-continuous tapes (in mm)
     */
    public function __construct(float $width, float $height, bool $continuous = true, float $spacing = 0.0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->continuous = $continuous;
        $this->spacing = $spacing;
        
        // Calculate base font size (7% of tape width)
        $baseFontSize = static::TAPE_WIDTH * 0.07;
        
        // Calculate margin (4% of tape width)
        $margin = static::TAPE_WIDTH * 0.04;
        
        // Set margins
        $this->marginTop = $margin;
        $this->marginBottom = $margin;
        $this->marginLeft = $margin;
        $this->marginRight = $margin;
        
        // Calculate and set element sizing based on base font size
        $this->titleSize = $baseFontSize;                // Same as base font size
        $this->titleMargin = $baseFontSize * 0.3;        // 30% of base font size
        $this->fieldSize = $baseFontSize * 1.1;          // 110% of base font size
        $this->fieldMargin = $baseFontSize * 0.1;        // 10% of base font size
        $this->labelSize = $baseFontSize * 0.7;          // 70% of base font size
        $this->labelMargin = $baseFontSize * -0.1;       // -10% of base font size
        $this->barcodeMargin = $baseFontSize * 0.5;      // 50% of base font size
        $this->tagSize = $baseFontSize * 0.8;            // 80% of base font size
    }
    
    // Unit of measurement
    public function getUnit()
    {
        return 'mm'; 
    }
    
    // Label dimensions
    public function getWidth()
    {
        return $this->width; 
    }
    public function getHeight()
    {
        return $this->height; 
    }
    
    // Margins
    public function getMarginTop()
    {
        return $this->marginTop; 
    }
    public function getMarginBottom()
    {
        return $this->marginBottom; 
    }
    public function getMarginLeft()
    {
        return $this->marginLeft; 
    }
    public function getMarginRight()
    {
        return $this->marginRight; 
    }

    
    /**
     * Check if this is a continuous tape
     * 
     * @return bool
     */
    public function isContinuous()
    {
        return $this->continuous;
    }
    
    /**
     * Get spacing between labels (for die-cut tapes)
     * 
     * @return float
     */
    public function getSpacing()
    {
        return $this->spacing;
    }
}