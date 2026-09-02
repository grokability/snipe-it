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
     * Returns the spacing between columns. Docblock only (no PHP
     * native return type) so subclasses that don't declare return
     * types stay compatible. Widened from int to int|float because
     * some sheet layouts (Hema/_14130046, Hema/_38310012) define
     * fractional spacings (2.0, 3.0, 4.0 mm) that would round
     * incorrectly if coerced to int.
     *
     * @return int|float
     */
    abstract public function getLabelColumnSpacing();

    /**
     * Returns the spacing between rows. See getLabelColumnSpacing for
     * the int|float rationale.
     *
     * @return int|float
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

    public function toEditorConfig(): array
    {
        return array_merge(parent::toEditorConfig(), [
            'grid' => $this->getGridEditorConfig(),
        ]);
    }

    protected function getGridEditorConfig(): array
    {
        return [
            'columns' => $this->getColumns(),
            'rows' => $this->getRows(),
            'column_spacing' => $this->getLabelColumnSpacing(),
            'row_spacing' => $this->getLabelRowSpacing(),
        ];
    }

    public function getEditorConfigSections(): array
    {
        return [
            'unit' => 'mm',
            'page' => $this->getPageEditorConfig(),
            'grid' => $this->getGridEditorConfig(),
            'printable_area' => $this->getPrintableAreaEditorConfig(),
            'label' => $this->getLabelEditorConfig(),
            'content' => $this->getContentEditorConfig(),
            'supports' => $this->getSupportsEditorConfig(),
        ];
    }

    public static function supportedPageSizes(): array
    {
        return [
            'letter' => [
                'name' => 'Letter (215.9mm x 279.4mm)',
                'width' => 215.9,
                'height' => 279.4,
                'unit' => 'mm',
            ],
            'a4' => [
                'name' => 'A4 (210mm x 297mm)',
                'width' => 210.0,
                'height' => 297.0,
                'unit' => 'mm',
            ],
        ];
    }

    public static function calculateGridCount(float $usableSize, float $labelSize, float $spacing): int
    {
        $denominator = $labelSize + $spacing;

        if ($denominator <= 0.0) {
            return 1;
        }

        $count = (int)floor(($usableSize + $spacing) / $denominator);

        return max(1, $count);
    }
    public static function supportedPageSize(string $key): ?array
    {
        return static::supportedPageSizes()[$key] ?? null;
    }
}
