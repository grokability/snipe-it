<?php

namespace App\Models\Labels;

use App\Helpers\Helper;
use App\Models\Setting;

class DefaultLabelAlternate extends RectangleSheet
{
    private const LOGO_MARGIN = 0.05;
    private const TEXT_MARGIN = 0.04;
    private const ASSET_TAG_OVERLAP = 3 / 5; // Tag number and 1D barcode overlap amount (1 = no overlap)

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


    public function __construct() {
        $settings = Setting::getSettings();

        $this->textSize = Helper::convertUnit($settings->labels_fontsize, 'pt', 'in');

        $this->labelWidth  = $settings->labels_width;
        $this->labelHeight = $settings->labels_height;

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

    public function getUnit()   { return 'in'; }

    public function getPageWidth()  { return $this->pageWidth; }
    public function getPageHeight() { return $this->pageHeight; }

    public function getPageMarginTop()    { return $this->pageMarginTop; }
    public function getPageMarginBottom() { return $this->pageMarginBottom; }
    public function getPageMarginLeft()   { return $this->pageMarginLeft; }
    public function getPageMarginRight()  { return $this->pageMarginRight; }

    public function getColumns() { return $this->columns; }
    public function getRows()    { return $this->rows; }
    public function getLabelBorder() { return 0; }

    public function getLabelWidth()  { return $this->labelWidth; }
    public function getLabelHeight() { return $this->labelHeight; }

    public function getLabelMarginTop()    { return 0; }
    public function getLabelMarginBottom() { return 0; }
    public function getLabelMarginLeft()   { return 0; }
    public function getLabelMarginRight()  { return 0; }

    public function getLabelColumnSpacing() { return $this->labelSpacingH; }
    public function getLabelRowSpacing()    { return $this->labelSpacingV; }

    public function getSupportAssetTag()  { return true; }
    public function getSupport1DBarcode() { return true; }
    public function getSupport2DBarcode() { return true; }
    public function getSupportFields()    { return 5; }
    public function getSupportTitle()     { return true; }
    public function getSupportLogo()      { return true; }

    public function preparePDF($pdf) {}

    public function write($pdf, $record) {

        $asset = $record->get('asset');
        $settings = Setting::getSettings();

        $textY = 0;// Default if no title
        $textX = 0;// Default if no logo
        $titleW = $this->getLabelWidth();// Default if no logo

        $BARCODE1D_SIZE = $this->getLabelHeight() * 0.18; // % of label height
        $BARCODE2D_SIZE = $this->getLabelHeight() * 0.48; // % of label height

        ## 1D Barcode ##
        $barcode1D['height'] = $BARCODE1D_SIZE;
        $barcode1D['width'] = $this->getLabelWidth() - ($this->pageMarginRight + $this->pageMarginLeft)* 2;

        if ($record->get('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $this->pageMarginLeft, $this->getLabelHeight() - $barcode1D['height'],
                $barcode1D['width'], $barcode1D['height']
            );
        }

        ## Logo ##
        if ($record->get('logo')) {
            // Calculate the available height for the logo
            $availableHeight = $this->getLabelHeight() - $BARCODE2D_SIZE - $barcode1D['height'] - self::LOGO_MARGIN * 2;

            // Get the aspect ratio of the logo
            $imageInfo = getimagesize($record->get('logo'));
            $aspectRatio = $imageInfo[0] / $imageInfo[1];

            // Calculate the width of the logo based on the aspect ratio and the available height
            $calculatedLogoWidth = $aspectRatio * $availableHeight;

            // Write the logo image with the calculated width and maintain aspect ratio
            static::writeImage(
                $pdf, $record->get('logo'),
                0, 0,
                $calculatedLogoWidth, null,
                'L', 'T', 300, true, false, 0
            );

            $textX += $calculatedLogoWidth + self::LOGO_MARGIN;
            $titleW -= $calculatedLogoWidth + self::LOGO_MARGIN;
        }

        ## Title ##
        if ($record->get('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $textX, 0,
                'freesans', 'b', $this->textSize, 'C',
                $titleW, $this->textSize,
                true, 0
            );
            $textY += $this->textSize;
        }

        // Calculate the y-position for the 2D barcode
        $barcode2DY = ($record->get('logo'))
            ? $this->getLabelHeight() - $barcode1D['height'] - $BARCODE2D_SIZE - self::LOGO_MARGIN
            : ($this->getLabelHeight() - $barcode1D['height'] - $BARCODE2D_SIZE + $textY) / 2 - self::TEXT_MARGIN;

        ## 2D Barcode ##
        if ($record->get('barcode2d')) {
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                0, $barcode2DY,
                $BARCODE2D_SIZE, $BARCODE2D_SIZE
            );
        }

        ## Fields ##
        $fields = $record->get('fields');
        $assetTagSet = isset($field['dataSource']) ? ($field['dataSource'] === 'asset_tag') : false;// Check if asset_tag field is set
        $fieldCount = ($assetTagSet) ? count($fields) + 1 : count($fields);// Count should exclude asset_tag field
        $totalFieldsHeight = $fieldCount * ($this->textSize + self::TEXT_MARGIN);// Calculate the total height occupied by the fields

        // Calculate the y-position to center the fields vertically within the label, between the title and barcode1D
        $centerY = ($this->getLabelHeight() + ($textY - self::TEXT_MARGIN) - $barcode1D['height'] - $totalFieldsHeight) / 2;

        // Using TCPDF function to get string length (in inches) to determine longest strings
        $maxLabelWidth = $maxValueWidth = 0;
        $pdf->SetFont('freemono', 'b', $settings->labels_fontsize);
        foreach ($fields as $field) {
            $maxLabelWidth = max($maxLabelWidth, $pdf->GetStringWidth($field['label']));
            $maxValueWidth = max($maxValueWidth, $pdf->GetStringWidth($field['value']));
        }

        // Calculate available width on the label for the fields
        $availableWidth = $this->getLabelWidth() - $BARCODE2D_SIZE - $this->pageMarginRight - $this->pageMarginLeft - self::TEXT_MARGIN;

        // Calculate combined width and scale factor
        $combinedWidth = $maxLabelWidth + $maxValueWidth + self::TEXT_MARGIN;
        $scaleFactor = ($combinedWidth > $availableWidth) ? ($availableWidth / $combinedWidth) : 1.0;

        // Apply scale factor to widths
        $maxLabelWidth *= $scaleFactor;
        $maxValueWidth *= $scaleFactor;

        // Calculate starting positions for label and value columns
        $labelStartX = $BARCODE2D_SIZE + self::TEXT_MARGIN;
        $valueStartX = $labelStartX + $maxLabelWidth;

        // Iterate over all the fields and write the fields to the label
        for ($fieldsDone = 0; $fieldsDone < min($this->getSupportFields(), $fieldCount); $fieldsDone++) {
            $field = $fields[$fieldsDone];
            $currentY = $centerY + $fieldsDone * ($this->textSize + self::TEXT_MARGIN);

            // If asset tag is set, write it centered above the barcode
            if ($field['dataSource'] === 'asset_tag') {
                // Calculate the X and Y position for centering the text
                $tagX = ($this->getLabelWidth() - $pdf->GetStringWidth($field['value'])) / 2;
                $tagY = $this->getLabelHeight() - $barcode1D['height'] - $this->textSize * self::ASSET_TAG_OVERLAP;

                // Write the text centered on the label
                static::writeText(
                    $pdf, $field['value'],
                    $tagX, $tagY,
                    'freemono', 'b', $this->textSize, 'C',
                    $pdf->GetStringWidth($field['value']), null, false, 0, null,true
                );

                // Adjust centerY to account for the asset tag height
                $centerY -= $this->textSize - self::TEXT_MARGIN;

            } else {// Write the rest of the fields
                // Write label text
                if ($field['label']) {
                    static::writeText(
                        $pdf, $field['label'],
                        $labelStartX, $currentY,
                        'freemono', 'b', $this->textSize, 'R',
                        $maxLabelWidth, null, true, 0
                    );
                }

                // Write value text
                static::writeText(
                    $pdf, $field['value'],
                    ($field['label']) ? $valueStartX + self::TEXT_MARGIN : $labelStartX, $currentY,
                    'freemono', 'b', $this->textSize, ($field['label']) ? 'L' : 'C',
                    ($field['label']) ? $maxValueWidth : $availableWidth, null, true, 0
                );
            }
        }

    }

}

?>