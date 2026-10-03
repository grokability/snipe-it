<?php

namespace App\Models\Labels\Sheets\Avery;

class _3490_A extends _3490
{
    private const BARCODE_MARGIN = 0.075;

    private const TAG_SIZE = 0.125;

    private const TITLE_SIZE = 0.140;

    private const TITLE_MARGIN = 0.040;

    private const LABEL_SIZE = 0.090;

    private const LABEL_MARGIN = -0.015;

    private const FIELD_SIZE = 0.150;

    private const FIELD_MARGIN = 0.012;
    protected string $titleFont = 'freesans';
    protected string $fieldLabelFont = 'freesans';
    protected string $fieldValueFont = 'freemono';

    public function getBarcodeMargin()
    {
        return self::BARCODE_MARGIN;
    }

    public function getTagSize()
    {
        return self::TAG_SIZE;
    }

    public function getTitleSize()
    {
        return self::TITLE_SIZE;
    }

    public function getTitleMargin()
    {
        return self::TITLE_MARGIN;
    }

    public function getLabelSize()
    {
        return self::LABEL_SIZE;
    }

    public function getLabelMargin()
    {
        return self::LABEL_MARGIN;
    }

    public function getFieldSize()
    {
        return self::FIELD_SIZE;
    }

    public function getFieldMargin()
    {
        return self::FIELD_MARGIN;
    }

    public function getUnit()
    {
        return 'in';
    }

    public function getLabelMarginTop()
    {
        return 0.06;
    }

    public function getLabelMarginBottom()
    {
        return 0.06;
    }

    public function getLabelMarginLeft()
    {
        return 0.06;
    }

    public function getLabelMarginRight()
    {
        return 0.06;
    }

    public function getSupportAssetTag()
    {
        return false;
    }

    public function getSupport1DBarcode()
    {
        return false;
    }

    public function getSupport2DBarcode()
    {
        return true;
    }

    public function getSupportFields()
    {
        return 3;
    }

    public function getSupportLogo()
    {
        return false;
    }

    public function getSupportTitle()
    {
        return true;
    }

    public function getTitleFont(): string
    {
        return $this->titleFont;
    }

    public function getFieldLabelFont(): string
    {
        return $this->fieldLabelFont;
    }

    public function getFieldValueFont(): string
    {
        return $this->fieldValueFont;
    }

    public function getTextRenderMode(): string
    {
        return 'vertical_stack';
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_margin' => $this->getBarcodeMargin(),
            'barcode_2d_size' => $this->getLabelPrintableArea()->h - (self::TITLE_SIZE + self::TITLE_MARGIN),
            'tag_font_size' => $this->getTagSize(),
            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),
            'title_position' => 'top',
            'title_offset_x' => 0.669291,
            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),
            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),
            'title_font' => $this->getTitleFont(),
            'field_label_font' => $this->getFieldLabelFont(),
            'field_value_font' => $this->getFieldValueFont(),
            'text_render_mode' => $this->getTextRenderMode(),
        ];
    }

    protected function getSupportsEditorConfig(): array
    {
        return [
            'asset_tag' => $this->getSupportAssetTag(),
            'barcode_1d' => $this->getSupport1DBarcode(),
            'barcode_2d' => $this->getSupport2DBarcode(),
            'fields' => $this->getSupportFields(),
            'logo' => $this->getSupportLogo(),
            'title' => $this->getSupportTitle(),
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getLabelPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $pa->x1, $pa->y1,
                'freesans', '', self::TITLE_SIZE, 'C',
                $pa->w, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
            $usableHeight -= self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        $barcodeSize = $usableHeight;
        if ($record->has('barcode2d')) {
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= $barcodeSize + self::BARCODE_MARGIN;
        }

        foreach ($record->get('fields') as $field) {
            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', '', self::LABEL_SIZE, 'L',
                $usableWidth, self::LABEL_SIZE, true, 0
            );
            $currentY += self::LABEL_SIZE + self::LABEL_MARGIN;

            static::writeText(
                $pdf, $field['value'],
                $currentX, $currentY,
                'freemono', 'B', self::FIELD_SIZE, 'L',
                $usableWidth, self::FIELD_SIZE, true, 0, 0.01
            );
            $currentY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }

    }
}
