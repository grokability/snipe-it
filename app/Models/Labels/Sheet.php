<?php

namespace App\Models\Labels;

abstract class Sheet extends Label
{
    protected int $indexOffset = 0;

    public function getWidth()
    {
        return $this->getPageWidth();
    }

    public function getHeight()
    {
        return $this->getPageHeight();
    }

    public function getMarginTop()
    {
        return $this->getPageMarginTop();
    }

    public function getMarginBottom()
    {
        return $this->getPageMarginBottom();
    }

    public function getMarginLeft()
    {
        return $this->getPageMarginLeft();
    }

    public function getMarginRight()
    {
        return $this->getPageMarginRight();
    }

    /**
     * Returns the page width in getUnit() units
     *
     * @return float
     */
    abstract public function getPageWidth();

    /**
     * Returns the page height in getUnit() units
     *
     * @return float
     */
    abstract public function getPageHeight();

    /**
     * Returns the page top margin in getUnit() units
     *
     * @return float
     */
    abstract public function getPageMarginTop();

    /**
     * Returns the page bottom margin in getUnit() units
     *
     * @return float
     */
    abstract public function getPageMarginBottom();

    /**
     * Returns the page left margin in getUnit() units
     *
     * @return float
     */
    abstract public function getPageMarginLeft();

    /**
     * Returns the page right margin in getUnit() units
     *
     * @return float
     */
    abstract public function getPageMarginRight();

    /**
     * Returns the page width in getUnit() units
     *
     * @return float
     */
    abstract public function getLabelWidth();

    /**
     * Returns each label's height in getUnit() units
     *
     * @return float
     */
    abstract public function getLabelHeight();

    /**
     * Returns each label's top margin in getUnit() units
     *
     * @return float
     */
    abstract public function getLabelMarginTop();

    /**
     * Returns each label's bottom margin in getUnit() units
     *
     * @return float
     */
    abstract public function getLabelMarginBottom();

    /**
     * Returns each label's left margin in getUnit() units
     *
     * @return float
     */
    abstract public function getLabelMarginLeft();

    /**
     * Returns each label's right margin in getUnit() units
     *
     * @return float
     */
    abstract public function getLabelMarginRight();

    /**
     * Returns the number of labels each page supports
     *
     * @return int
     */
    abstract public function getLabelsPerPage();

    /**
     * Returns label position based on its index
     *
     *
     * @return array [x,y]
     */
    abstract public function getLabelPosition(int $index);

    /**
     * Returns the border to draw around labels
     *
     * @return int
     */
    abstract public function getLabelBorder();

    /**
     * Handle the data here. Override for multiple-per-page handling
     *
     * @param  TCPDF  $pdf  The TCPDF instance
     * @param  Collection  $data  The data
     */
    public function writeAll($pdf, $data)
    {
        $prevPageNumber = -1;

        foreach ($data->toArray() as $recordIndex => $record) {

            $pageNumber = (int) ($recordIndex / $this->getLabelsPerPage());
            if ($pageNumber != $prevPageNumber) {
                $pdf->AddPage();
                $prevPageNumber = $pageNumber;
            }

            $pageIndex = $recordIndex - ($this->getLabelsPerPage() * $pageNumber);
            $position = $this->getLabelPosition($pageIndex);

            $pdf->StartTemplate();
            $this->write($pdf, $data->get($recordIndex));
            $template = $pdf->EndTemplate();

            $pdf->printTemplate($template, $position[0], $position[1]);

            if ($this->getLabelBorder()) {
                $prevLineWidth = $pdf->GetLineWidth();

                $borderThickness = $this->getLabelBorder();
                $borderOffset = $borderThickness / 2;
                $borderX = $position[0] - $borderOffset;
                $borderY = $position[1] - $borderOffset;
                $borderW = $this->getLabelWidth() + $borderThickness;
                $borderH = $this->getLabelHeight() + $borderThickness;

                $pdf->setLineWidth($borderThickness);
                $pdf->Rect($borderX, $borderY, $borderW, $borderH);
                $pdf->setLineWidth($prevLineWidth);
            }
        }
    }

    /**
     * Returns each label's orientation as a string.
     * 'L' = Landscape
     * 'P' = Portrait
     *
     * @return string
     */
    final public function getLabelOrientation()
    {
        return ($this->getLabelWidth() >= $this->getLabelHeight()) ? 'L' : 'P';
    }

    /**
     * Returns each label's printable area as an object.
     *
     * @return object [ 'x1'=>0.00, 'y1'=>0.00, 'x2'=>0.00, 'y2'=>0.00, 'w'=>0.00, 'h'=>0.00 ]
     */
    final public function getLabelPrintableArea()
    {
        return (object) [
            'x1' => $this->getLabelMarginLeft(),
            'y1' => $this->getLabelMarginTop(),
            'x2' => $this->getLabelWidth() - $this->getLabelMarginRight(),
            'y2' => $this->getLabelHeight() - $this->getLabelMarginBottom(),
            'w' => $this->getLabelWidth() - $this->getLabelMarginLeft() - $this->getLabelMarginRight(),
            'h' => $this->getLabelHeight() - $this->getLabelMarginTop() - $this->getLabelMarginBottom(),
        ];
    }

    /**
     * Returns label index offset (skip positions)
     *
     * @return int
     */
    public function getLabelIndexOffset()
    {
        return $this->indexOffset;
    }

    /**
     * Sets label index offset (skip positions)
     */
    public function setLabelIndexOffset(int $offset)
    {
        $this->indexOffset = $offset;
    }

    public function toEditorConfig(): array
    {
        return array_merge(parent::toEditorConfig(), [
            'page' => $this->getPageEditorConfig(),
            'label' => $this->getLabelEditorConfig(),
            'label_printable_area' => $this->getLabelPrintableAreaEditorConfig(),
            'sheet' => $this->getSheetEditorConfig(),
        ]);
    }

    protected function getPageEditorConfig(): array
    {
        return [
            'width' => $this->getPageWidth(),
            'height' => $this->getPageHeight(),
            'margin_top' => $this->getPageMarginTop(),
            'margin_right' => $this->getPageMarginRight(),
            'margin_bottom' => $this->getPageMarginBottom(),
            'margin_left' => $this->getPageMarginLeft(),
        ];
    }

    protected function getLabelEditorConfig(): array
    {
        return [
            'width' => $this->getLabelWidth(),
            'height' => $this->getLabelHeight(),
            'border' => $this->getLabelBorder(),
            'padding_top' => $this->getLabelMarginTop(),
            'padding_right' => $this->getLabelMarginRight(),
            'padding_bottom' => $this->getLabelMarginBottom(),
            'padding_left' => $this->getLabelMarginLeft(),
        ];
    }

    protected function getLabelPrintableAreaEditorConfig(): array
    {
        $pa = $this->getLabelPrintableArea();

        return [
            'x1' => $pa->x1,
            'y1' => $pa->y1,
            'x2' => $pa->x2,
            'y2' => $pa->y2,
            'width' => $pa->w,
            'height' => $pa->h,
        ];
    }

    protected function getSheetEditorConfig(): array
    {
        return [
            'labels_per_page' => $this->getLabelsPerPage(),
            'index_offset' => $this->getLabelIndexOffset(),
            'label_orientation' => $this->getLabelOrientation(),
        ];
    }
}
