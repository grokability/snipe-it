<?php

namespace App\Models\Labels\Tapes\Generic;

abstract class Tape_53mm extends GenericTape
{
    // Override tape width to 53mm
    protected const TAPE_WIDTH = 53.0;
    
    private float $tapeHeight;
    
    /**
     * Constructor for 53mm tape
     *
     * @param float $height     Height of the label in mm (default 30mm)
     * @param bool  $continuous Whether the tape is continuous or pre-cut
     * @param float $spacing    Spacing between labels for non-continuous tapes (in mm)
     */
    public function __construct(float $height = 30.0, bool $continuous = true, float $spacing = 0.0)
    {
        parent::__construct(self::TAPE_WIDTH, $height, $continuous, $spacing);
        $this->tapeHeight = $height;
    }
    
    /**
     * Get the barcode size ratio for calculations
     *
     * @return float
     */
    public function getBarcodeRatio()
    {
        return 0.9;  // Barcode should use 90% of available width
    }
}