<?php

namespace App\Models\Labels\Tapes\Brother;

use App\Helpers\Helper;
use App\Models\Labels\Label;

abstract class TZe_12mm extends Label
{
    private const HEIGHT       = 12.00;
    private const MARGIN_SIDES =  3.20;
    private const MARGIN_ENDS  =  3.20;

    public function getHeight()
    {
        return Helper::convertUnit(self::HEIGHT, 'mm', $this->getUnit()); 
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
}