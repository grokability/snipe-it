<?php

namespace App\Models\Labels\Sheets\Avery;

use App\Helpers\Helper;
use App\Models\Labels\RectangleSheet;

abstract class _3490 extends RectangleSheet
{

    private const PAPER_FORMAT      = 'A4';
    private const PAPER_ORIENTATION = 'P';

    /* Data in pt from Word Template */
    private const COLUMN1_X = 5.00;
    private const COLUMN2_X = 198.4;
    private const ROW1_Y    = 10.00;
    private const ROW2_Y    = 112.00;
    private const LABEL_W   = 193.4;
    private const LABEL_H   = 102.00;


    private float $pageWidth;
    private float $pageHeight;
    private float $pageMarginLeft;
    private float $pageMarginTop;

    private float $columnSpacing;
    private float $rowSpacing;

    private float $labelWidth;
    private float $labelHeight;

    public function __construct()
    {
        $paperSize = static::fromFormat(self::PAPER_FORMAT, self::PAPER_ORIENTATION, $this->getUnit(), 2);
        $this->pageWidth = $paperSize->width;
        $this->pageHeight = $paperSize->height;

        $this->pageMarginLeft = Helper::convertUnit(self::COLUMN1_X, 'pt', $this->getUnit());
        $this->pageMarginTop = Helper::convertUnit(self::ROW1_Y, 'pt', $this->getUnit());

        $columnSpacingPt = self::COLUMN2_X - self::COLUMN1_X - self::LABEL_W;
        $this->columnSpacing = Helper::convertUnit($columnSpacingPt, 'pt', $this->getUnit());
        $rowSpacingPt = self::ROW2_Y - self::ROW1_Y - self::LABEL_H;
        $this->rowSpacing = Helper::convertUnit($rowSpacingPt, 'pt', $this->getUnit());

        $this->labelWidth = Helper::convertUnit(self::LABEL_W, 'pt', $this->getUnit());
        $this->labelHeight = Helper::convertUnit(self::LABEL_H, 'pt', $this->getUnit());
    }

    public function getPageWidth()
    {
        return $this->pageWidth; 
    }
    public function getPageHeight()
    {
        return $this->pageHeight; 
    }

    public function getPageMarginTop()
    {
        return $this->pageMarginTop; 
    }
    public function getPageMarginBottom()
    {
        return $this->pageMarginTop; 
    }
    public function getPageMarginLeft()
    {
        return $this->pageMarginLeft; 
    }
    public function getPageMarginRight()
    {
        return $this->pageMarginLeft; 
    }

    public function getColumns()
    {
        return 3; 
    }
    public function getRows()
    {
        return 10; 
    }

    public function getLabelColumnSpacing()
    {
        return $this->columnSpacing; 
    }
    public function getLabelRowSpacing()
    {
        return $this->rowSpacing; 
    }

    public function getLabelWidth()
    {
        return $this->labelWidth; 
    }
    public function getLabelHeight()
    {
        return $this->labelHeight; 
    }

    public function getLabelBorder()
    {
        return 0; 
    }
}

?>