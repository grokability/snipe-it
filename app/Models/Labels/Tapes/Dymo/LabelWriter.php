<?php

namespace App\Models\Labels\Tapes\Dymo;

use App\Helpers\Helper;
use App\Models\Labels\Label;

abstract class LabelWriter extends Label
{
    private const HEIGHT       = 1.15;
    private const MARGIN_SIDES =  0.1;
    private const MARGIN_ENDS  =  0.1;

    public function getHeight()
    {
        return Helper::convertUnit(self::HEIGHT, 'in', $this->getUnit()); 
    }
    public function getMarginTop()
    {
        return Helper::convertUnit(self::MARGIN_SIDES, 'in', $this->getUnit()); 
    }
    public function getMarginBottom()
    {
        return Helper::convertUnit(self::MARGIN_SIDES, 'in', $this->getUnit());
    }
    public function getMarginLeft()
    {
        return Helper::convertUnit(self::MARGIN_ENDS, 'in', $this->getUnit()); 
    }
    public function getMarginRight()
    {
        return Helper::convertUnit(self::MARGIN_ENDS, 'in', $this->getUnit()); 
    }
}