<?php

namespace App\Models\Labels\CustomLabels\Concerns;

trait HasCustomLabelContentProperties
{
    protected float $barcodeSize = 3.0;
    protected float $barcodeMargin = 0.3;
    protected float $barcode2DSize = 10.0;
    protected string $barcode2DHAlign = 'L';
    protected string $barcode2DVAlign = 'T';
    protected float $tagSize = 5.5;
    protected string $tagAlignment = 'L';
    protected string $tagFont = 'freemono';
    protected float $tagOffsetX = 0.0;
    protected float $tagOffsetY = 0.0;

    protected float $titleSize = 8.0;
    protected float $titleMargin = 1.0;
    protected float $titleOffsetX = 0.0;
    protected string $titleFont = 'freesans';

    protected float $labelSize = 5.0;
    protected float $labelMargin = 1.0;
    protected string $fieldLabelFont = 'freesans';

    protected float $fieldSize = 5.0;
    protected float $fieldMargin = 1.0;
    protected string $fieldValueFont = 'freemono';

    protected float $logoMaxWidth = 12.0;
    protected float $logoMargin = 2.0;
    protected string $logoHAlign = 'L';
    protected string $logoVAlign = 'T';

    //Tape only properities
    protected string $barcode1DVAlign = 'M';
    protected string $barcode1DPlacement = 'inline';

    protected float $barcode2DMargin = 0.0;
    protected string $barcode2DPlacement = 'inline';

    protected string $tagPositionMode = 'inline';

    protected string $fieldAlignment = 'L';

    protected float $logoMaxHeight = 12.0;
    protected string $logoPlacement = 'inline';

    protected float $textSizeMod = 1.0;
    protected float $textAreaOffsetY = 0.0;
    protected string $textRenderMode = 'normal';
    protected string $titlePosition = 'inline';

    protected ?float $textAreaWidth = null;
    protected ?float $textAreaHeight = null;

    public function getBarcodeSize(): float
    {
        return $this->barcodeSize;
    }

    public function getBarcodeMargin(): float
    {
        return $this->barcodeMargin;
    }

    public function get2DBarcodeSize(): float
    {
        return $this->barcode2DSize;
    }

    public function getBarcode2DHAlign(): string
    {
        return $this->barcode2DHAlign;
    }

    public function getBarcode2DVAlign(): string
    {
        return $this->barcode2DVAlign;
    }

    public function getTagSize(): float
    {
        return $this->tagSize;
    }

    public function getTagAlignment(): string
    {
        return $this->tagAlignment;
    }

    public function getTagFont(): string
    {
        return $this->tagFont;
    }

    public function getTagOffsetX(): float
    {
        return $this->tagOffsetX;
    }

    public function getTagOffsetY(): float
    {
        return $this->tagOffsetY;
    }

    public function getTitleSize(): float
    {
        return $this->titleSize;
    }

    public function getTitleMargin(): float
    {
        return $this->titleMargin;
    }

    public function getTitleOffsetX(): float
    {
        return $this->titleOffsetX;
    }

    public function getTitlePosition(): string
    {
        return $this->titlePosition;
    }
    public function getTitleFont(): string
    {
        return $this->titleFont;
    }

    public function getLabelSize(): float
    {
        return $this->labelSize;
    }

    public function getLabelMargin(): float
    {
        return $this->labelMargin;
    }

    public function getFieldSize(): float
    {
        return $this->fieldSize;
    }

    public function getFieldMargin(): float
    {
        return $this->fieldMargin;
    }

    public function getFieldLabelFont(): string
    {
        return $this->fieldLabelFont;
    }

    public function getFieldValueFont(): string
    {
        return $this->fieldValueFont;
    }

    public function getLogoMaxWidth(): float
    {
        return $this->logoMaxWidth;
    }

    public function getLogoMargin(): float
    {
        return $this->logoMargin;
    }

    public function getLogoHAlign(): string
    {
        return $this->logoHAlign;
    }

    public function getLogoVAlign(): string
    {
        return $this->logoVAlign;
    }

    public function getTextAreaWidth(): ?float
    {
        return $this->textAreaWidth;
    }

    public function getTextAreaHeight(): ?float
    {
        return $this->textAreaHeight;
    }

    protected function hydrateSheetPage(array $page): void
    {
        $this->pageWidth = isset($page['width']) ? (float)$page['width'] : $this->pageWidth;
        $this->pageHeight = isset($page['height']) ? (float)$page['height'] : $this->pageHeight;
        $this->pageMarginTop = isset($page['margin_top']) ? (float)$page['margin_top'] : $this->pageMarginTop;
        $this->pageMarginRight = isset($page['margin_right']) ? (float)$page['margin_right'] : $this->pageMarginRight;
        $this->pageMarginBottom = isset($page['margin_bottom']) ? (float)$page['margin_bottom'] : $this->pageMarginBottom;
        $this->pageMarginLeft = isset($page['margin_left']) ? (float)$page['margin_left'] : $this->pageMarginLeft;
    }

    protected function hydrateSheetGrid(array $grid): void
    {
        $this->rows = isset($grid['rows']) ? (int)$grid['rows'] : $this->rows;
        $this->columns = isset($grid['columns']) ? (int)$grid['columns'] : $this->columns;
        $this->labelRowSpacing = isset($grid['row_spacing']) ? (float)$grid['row_spacing'] : $this->labelRowSpacing;
        $this->labelColumnSpacing = isset($grid['column_spacing']) ? (float)$grid['column_spacing'] : $this->labelColumnSpacing;
    }

    protected function hydrateSheetLabel(array $label): void
    {
        $this->labelWidth = isset($label['width']) ? (float)$label['width'] : $this->labelWidth;
        $this->labelHeight = isset($label['height']) ? (float)$label['height'] : $this->labelHeight;
        $this->labelMarginTop = isset($label['padding_top']) ? (float)$label['padding_top'] : $this->labelMarginTop;
        $this->labelMarginRight = isset($label['padding_right']) ? (float)$label['padding_right'] : $this->labelMarginRight;
        $this->labelMarginBottom = isset($label['padding_bottom']) ? (float)$label['padding_bottom'] : $this->labelMarginBottom;
        $this->labelMarginLeft = isset($label['padding_left']) ? (float)$label['padding_left'] : $this->labelMarginLeft;
    }

    protected function hydrateContent(array $content): void
    {
        $this->hydrateNumericContent($content);
        $this->hydrateStringContent($content);

        if (array_key_exists('barcode2D_h_align', $content)) {
            $this->syncLogoAnd2DBarcodeHAlign('barcode2D_h_align');
        } elseif (array_key_exists('logo_h_align', $content)) {
            $this->syncLogoAnd2DBarcodeHAlign('logo_h_align');
        }
    }

    protected function hydrateNumericContent(array $content): void
    {
        foreach ($this->editorNumericContentMap() as $key => $property) {
            if (
                array_key_exists($key, $content)
                && $content[$key] !== ''
                && $content[$key] !== null
            ) {
                $this->{$property} = (float)$content[$key];
            }
        }
    }

    protected function hydrateStringContent(array $content): void
    {
        foreach ($this->editorStringContentMap() as $key => $property) {
            if (
                array_key_exists($key, $content)
                && $content[$key] !== ''
                && $content[$key] !== null
            ) {
                $this->{$property} = (string)$content[$key];
            }
        }
    }
}