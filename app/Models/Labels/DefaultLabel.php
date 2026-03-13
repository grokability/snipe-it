<?php

namespace App\Models\Labels;

use App\Helpers\Helper;
use App\Models\Setting;

class DefaultLabel extends RectangleSheet
{
    private const BARCODE1D_SIZE = 0.15;

    private const BARCODE2D_SIZE = 0.76;
    private const BARCODE2D_MARGIN = 0.075;

    private const LOGO_SIZE = [0.75, 0.50];
    private const LOGO_MARGIN = 0.05;

    private const TEXT_MARGIN = 0.04;


    private float $textSize;

    private float $labelWidth;
    private float $labelHeight;

    private float $labelSpacingH;
    private float $labelSpacingV;

    private float $pageMarginTop;
    private float $pageMarginBottom;
    private float $pageMarginLeft;
    private float $pageMarginRight;

    private float $pageWidth;
    private float $pageHeight;

    private int $columns;
    private int $rows;


    public function __construct()
    {
        $settings = Setting::getSettings();

        $this->textSize = Helper::convertUnit($settings->labels_fontsize, 'pt', 'in');

        $this->labelWidth = $this->setLabelWidth($settings);
        $this->labelHeight = $this->setLabelHeight($settings);

        $this->labelSpacingH = $settings->labels_display_sgutter;
        $this->labelSpacingV = $settings->labels_display_bgutter;

        $this->pageMarginTop    = $settings->labels_pmargin_top;
        $this->pageMarginBottom = $settings->labels_pmargin_bottom;
        $this->pageMarginLeft   = $settings->labels_pmargin_left;
        $this->pageMarginRight  = $settings->labels_pmargin_right;

        $this->pageWidth  = $settings->labels_pagewidth;
        $this->pageHeight = $settings->labels_pageheight;

        $usableWidth = $this->pageWidth - $this->pageMarginLeft - $this->pageMarginRight;
        $usableHeight = $this->pageHeight - $this->pageMarginTop - $this->pageMarginBottom;

        $this->columns = ($usableWidth + $this->labelSpacingH) / ($this->labelWidth + $this->labelSpacingH);
        $this->rows = ($usableHeight + $this->labelSpacingV) / ($this->labelHeight + $this->labelSpacingV);

        // Make sure the columns and rows are never zero, since that scenario should never happen
        if ($this->columns == 0) {
            $this->columns = 1;
        }

        if ($this->rows == 0) {
            $this->rows = 1;
        }

    }

    public function getUnit()
    {
        return 'in'; 
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
        return $this->pageMarginBottom; 
    }
    public function getPageMarginLeft()
    {
        return $this->pageMarginLeft; 
    }
    public function getPageMarginRight()
    {
        return $this->pageMarginRight; 
    }

    public function getColumns()
    {
        return $this->columns; 
    }
    public function getRows()
    {
        return $this->rows; 
    }
    public function getLabelBorder()
    {
        return 0; 
    }

    public function getLabelWidth()
    {
        return $this->labelWidth; 
    }
    public function getLabelHeight()
    {
        return $this->labelHeight; 
    }

    public function getLabelMarginTop()
    {
        return 0; 
    }
    public function getLabelMarginBottom()
    {
        return 0; 
    }
    public function getLabelMarginLeft()
    {
        return 0; 
    }
    public function getLabelMarginRight()
    {
        return 0; 
    }

    public function getLabelColumnSpacing()
    {
        return $this->labelSpacingH; 
    }
    public function getLabelRowSpacing()
    {
        return $this->labelSpacingV; 
    }

    public function getSupportAssetTag()
    {
        return false; 
    }
    public function getSupport1DBarcode()
    {
        return true; 
    }
    public function getSupport2DBarcode()
    {
        return true; 
    }
    public function getSupportFields()
    {
        return 4; 
    }
    public function getSupportTitle()
    {
        return true; 
    }
    public function getSupportLogo()
    {
        return true; 
    }

    public function preparePDF($pdf)
    {
    }

    public function write($pdf, $record)
    {

        $asset = $record->get('asset');
        $settings = Setting::getSettings();

        $textY = 0;
        $textX1 = 0;
        $textX2 = $this->getLabelWidth();

        // 1D Barcode
        if ($record->get('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                0.05, $this->getLabelHeight() - self::BARCODE1D_SIZE,
                $this->getLabelWidth() - 0.1, self::BARCODE1D_SIZE
            );
        }

        // 2D Barcode
        if ($record->get('barcode2d')) {
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                0, 0, self::BARCODE2D_SIZE, self::BARCODE2D_SIZE
            );
            $textX1 += self::BARCODE2D_SIZE + self::BARCODE2D_MARGIN;
        }

        // Logo
        if ($record->get('logo')) {
            $logoSize = static::writeImage(
                $pdf, $record->get('logo'),
                $this->labelWidth - self::LOGO_SIZE[0], 0,
                self::LOGO_SIZE[0], self::LOGO_SIZE[1],
                'R', 'T', 300, true, false, 0
            );
            $textX2 -= ($logoSize[0] + self::LOGO_MARGIN);
        }

        $textW = $textX2 - $textX1;

        // Title
        if ($record->get('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $textX1, 0,
                Helper::isCjk($record->get('title')) ? 'cid0cs' : 'freesans', 'b', $this->textSize, 'L',
                $textW, $this->textSize,
                true, 0
            );
            $textY += $this->textSize + self::TEXT_MARGIN;
        }

        // Render the selected fields with their labels
        $fieldsDone = 0;
        if ($fieldsDone < $this->getSupportFields()) {

            foreach ($record->get('fields') as $field) {

                // Actually write the selected fields and their matching values
                static::writeText(
                    $pdf, (($field['label']) ? $field['label'].' ' : '') . $field['value'],
                    $textX1, $textY,
                    Helper::isCjk($field['label']) ? 'cid0cs' : 'freesans', '', $this->textSize, 'L',
                    $textW, $this->textSize,
                    true, 0
                );


                $textY += $this->textSize + self::TEXT_MARGIN;
                $fieldsDone++;
            }
        }
    }

    private function setLabelWidth(Setting $settings)
    {
        $labelWidth = $settings->labels_width;

        if ($labelWidth == 0) {
            $labelWidth = 0.1;
        }

        return $labelWidth;
    }

    private function setLabelHeight(?Setting $settings)
    {
        $labelHeight = $settings->labels_height;

        if ($labelHeight == 0) {
            $labelHeight = 0.1;
        }

        return $labelHeight;
    }
}
