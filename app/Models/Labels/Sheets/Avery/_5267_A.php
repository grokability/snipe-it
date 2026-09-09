<?php

namespace App\Models\Labels\Sheets\Avery;

class _5267_A extends _5267
{
    private const BARCODE_SIZE = 0.175;

    private const BARCODE_MARGIN = 0.000;

    private const TAG_SIZE = 0.125;

    private const TITLE_SIZE = 0.140;

    private const FIELD_SIZE = 0.150;

    private const FIELD_MARGIN = 0.012;
    protected string $titleFont = 'freesans';
    protected string $fieldLabelFont = 'freesans';
    protected string $fieldValueFont = 'freemono';

    public function getBarcodeSize()
    {
        return self::BARCODE_SIZE;
    }

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
        return 0.02;
    }

    public function getLabelMarginBottom()
    {
        return 0.00;
    }

    public function getLabelMarginLeft()
    {
        return 0.04;
    }

    public function getLabelMarginRight()
    {
        return 0.04;
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
        return false;
    }

    public function getSupportFields()
    {
        return 1;
    }

    public function getSupportLogo()
    {
        return false;
    }

    public function getSupportTitle()
    {
        return true;
    }

    public function get2DBarcodeSize()
    {
        $pa = $this->getLabelPrintableArea();

        $barcode2dSize = $pa->h;

        if ($this->getSupportTitle()) {
            $barcode2dSize -= $this->getTitleSize();
        }

        return $barcode2dSize;
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

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_size' => $this->getBarcodeSize(),
            'barcode_margin' => $this->getBarcodeMargin(),
            '2dbarcode_size' => $this->get2DBarcodeSize(),
            'tag_font_size' => $this->getTagSize(),
            'title_font_size' => $this->getTitleSize(),
            'field_label_font_size' => 0.001,
            'field_value_font_size' => .3,
            'field_label_margin' => .25,
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
            'title_font' => $this->getTitleFont(),
            'field_label_font' => $this->getFieldLabelFont(),
            'field_value_font' => $this->getFieldValueFont(),
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getLabelPrintableArea();

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $pa->x1, $pa->y2 - self::BARCODE_SIZE,
                $pa->w, self::BARCODE_SIZE
            );
        }

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $pa->x1, $pa->y1,
                'freesans', '', self::TITLE_SIZE, 'L',
                $pa->w, self::TITLE_SIZE, true, 0
            );
        }

        $fieldY = $pa->y2 - self::BARCODE_SIZE - self::BARCODE_MARGIN - self::FIELD_SIZE;
        if ($record->has('fields')) {
            if ($record->get('fields')->count() >= 1) {
                $field = $record->get('fields')->first();
                static::writeText(
                    $pdf, $field['value'],
                    $pa->x1, $fieldY,
                    'freemono', 'B', self::FIELD_SIZE, 'C',
                    $pa->w, self::FIELD_SIZE, true, 0, 0.01
                );
            }
        }

    }
}
