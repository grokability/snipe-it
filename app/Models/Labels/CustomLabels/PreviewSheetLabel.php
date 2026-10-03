<?php

namespace App\Models\Labels\CustomLabels;

class PreviewSheetLabel extends CustomSheetLabel
{
    protected bool $supportAssetTag = true;
    protected bool $support1DBarcode = true;
    protected bool $support2DBarcode = true;
    protected int $supportFields = 5;
    protected bool $supportLogo = true;
    protected bool $supportTitle = true;
    public function __construct(
        float $pageWidth = 210.0,
        float $pageHeight = 297.0,
        float $labelWidth = 50.0,
        float $labelHeight = 25.0,
        int $rows = 9,
        int $columns = 3,
    )
    {
        $this->pageWidth = $pageWidth;
        $this->pageHeight = $pageHeight;
        $this->labelWidth = $labelWidth;
        $this->labelHeight = $labelHeight;
        $this->rows = $rows;
        $this->columns = $columns;
    }

    protected function hydrateFromEditorConfig(array $config): void
    {
        parent::hydrateFromEditorConfig($config);

        $grid = $config['grid'] ?? [];

        $this->rows = isset($grid['rows'])
            ? (int)$grid['rows']
            : $this->rows;

        $this->columns = isset($grid['columns'])
            ? (int)$grid['columns']
            : $this->columns;

        $this->labelRowSpacing = isset($grid['row_spacing'])
            ? (float)$grid['row_spacing']
            : $this->labelRowSpacing;

        $this->labelColumnSpacing = isset($grid['column_spacing'])
            ? (float)$grid['column_spacing']
            : $this->labelColumnSpacing;
    }
}
