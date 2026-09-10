<?php

namespace App\Models\Labels\Tapes\Brother;

use App\Helpers\Helper;
use App\Models\Labels\Label;

abstract class TZe_25mm extends Label
{
    private const HEIGHT = 36.00;

    private const MARGIN_SIDES = 2.00;

    private const MARGIN_ENDS = 2.00;

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
    public function getRotation()
    {
        return 90;
    }
}
