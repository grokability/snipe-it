<?php

namespace App\Models\Labels\Tapes\Brother;

use App\Helpers\Helper;
use App\Models\Labels\Label;

/*
 * Rotated Label (print direction = landscape) for 62mm wide labels
 */
abstract class TZe_62mm_Landscape extends Label
{
    private const WIDTH        = 62.00;
    private const MARGIN_SIDES =  1.50;
    private const MARGIN_ENDS  =  1.50;

    public function getWidth()
    {
        return Helper::convertUnit(self::WIDTH, 'mm', $this->getUnit()); 
    }
    public function getMarginTop()
    {
        return Helper::convertUnit(self::MARGIN_SIDES, 'mm', $this->getUnit()); 
    }
    public function getMarginBottom()
    {
        return Helper::convertUnit(self::MARGIN_SIDES, 'mm', $this->getUnit());
    }
    public function getMarginLeft()
    {
        return Helper::convertUnit(self::MARGIN_ENDS, 'mm', $this->getUnit()); 
    }
    public function getMarginRight()
    {
        return Helper::convertUnit(self::MARGIN_ENDS, 'mm', $this->getUnit()); 
    }
    public function getRotation()
    {
        return 90; 
    }
}
