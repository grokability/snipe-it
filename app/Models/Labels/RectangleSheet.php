<?php

namespace App\Models\Labels;

abstract class RectangleSheet extends Sheet
{
    /**
     * Returns the number of columns per sheet
     *
     * @return int
     */
    abstract public function getColumns();

    /**
     * Returns the number of rows per sheet
     *
     * @return int
     */
    abstract public function getRows();

    /**
     * Returns the spacing between columns
     *
     * @return int
     */
    abstract public function getLabelColumnSpacing();

    /**
     * Returns the spacing between rows
     *
     * @return int
     */
    abstract public function getLabelRowSpacing();

    public function getLabelsPerPage()
    {
        return $this->getColumns() * $this->getRows();
    }

    public function getLabelPosition($index)
    {
        $printIndex = $index + $this->getLabelIndexOffset();
        $row = (int) ($printIndex / $this->getColumns());
        $col = $printIndex - ($row * $this->getColumns());
        $x = $this->getPageMarginLeft() + (($this->getLabelWidth() + $this->getLabelColumnSpacing()) * $col);
        $y = $this->getPageMarginTop() + (($this->getLabelHeight() + $this->getLabelRowSpacing()) * $row);

        return [$x, $y];
    }
}
