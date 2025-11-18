<?php

namespace App\Models\Labels\Sheets\Avery;

use App\Helpers\Helper;
use App\Models\Labels\RectangleSheet;

abstract class L6009 extends RectangleSheet
{
    private const PAPER_FORMAT      = 'A4';
    private const PAPER_ORIENTATION = 'P';

    // Convert mm to pt (1 mm = 2.83465 pt)
    private const LABEL_W        = 129.54;
    private const LABEL_H        =  60.09;
    private const COLUMN1_X      =  22.67;
    private const ROW1_Y         =  37.41;
    private const COLUMN_SPACING =  14.17;
    private const ROW_SPACING    =   2.26;

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
        $paperSize = static::fromFormat(self::PAPER_FORMAT, self::PAPER_ORIENTATION, $this->getUnit(), 0);
        $this->pageWidth = $paperSize->width;
        $this->pageHeight = $paperSize->height;

        $this->pageMarginLeft = Helper::convertUnit(self::COLUMN1_X, 'pt', $this->getUnit());
        $this->pageMarginTop = Helper::convertUnit(self::ROW1_Y, 'pt', $this->getUnit());

        $this->columnSpacing = Helper::convertUnit(self::COLUMN_SPACING, 'pt', $this->getUnit());
        $this->rowSpacing = Helper::convertUnit(self::ROW_SPACING, 'pt', $this->getUnit());

        $this->labelWidth = Helper::convertUnit(self::LABEL_W, 'pt', $this->getUnit());
        $this->labelHeight = Helper::convertUnit(self::LABEL_H, 'pt', $this->getUnit());
    }

    public function getPageWidth()         { return $this->pageWidth; }
    public function getPageHeight()        { return $this->pageHeight; }

    public function getPageMarginTop()     { return $this->pageMarginTop; }
    public function getPageMarginBottom()  { return $this->pageMarginTop; }
    public function getPageMarginLeft()    { return $this->pageMarginLeft; }
    public function getPageMarginRight()   { return $this->pageMarginLeft; }

    public function getColumns()           { return 4; }
    public function getRows()              { return 12; }

    public function getLabelColumnSpacing(){ return $this->columnSpacing; }
    public function getLabelRowSpacing()   { return $this->rowSpacing; }

    public function getLabelWidth()        { return $this->labelWidth; }
    public function getLabelHeight()       { return $this->labelHeight; }

    public function getLabelBorder()       { return 0; }
}

?>