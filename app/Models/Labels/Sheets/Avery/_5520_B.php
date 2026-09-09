<?php

namespace App\Models\Labels\Sheets\Avery;

class _5520_B extends _5520
{
    private const BARCODE_SIZE = 0.20;

    private const BARCODE_MARGIN = 1.40;

    private const TAG_SIZE = 0.125;

    private const TITLE_SIZE = 0.140;

    private const TITLE_MARGIN = 0.025;

    private const LABEL_SIZE = 0.090;

    private const LABEL_MARGIN = -0.015;

    private const FIELD_SIZE = 0.150;

    private const FIELD_MARGIN = 0.012;

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
        return true;
    }

    public function getSupport2DBarcode()
    {
        return false;
    }

    public function getSupportFields()
    {
        return 2;
    }

    public function getSupportLogo()
    {
        return false;
    }

    public function getSupportTitle()
    {
        return true;
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_size' => .15,
            'barcode_margin' => .025,
            'tag_font_size' => $this->getTagSize(),
            'title_font_size' => 0.0826772,
            'title_margin' => $this->getTitleMargin(),
            'title_offset_x' => 0.629921,
            'field_label_font_size' => 0.09,
            'field_label_margin' => $this->getLabelMargin(),
            'field_value_font_size' => 0.1106299,
            'field_value_margin' => $this->getFieldMargin(),
            'text_render_mode' => 'vertical_stack'
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

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $pa->x1, $pa->y2 - self::BARCODE_SIZE,
                $usableWidth, self::BARCODE_SIZE
            );
            $usableHeight -= self::BARCODE_SIZE + self::BARCODE_MARGIN;
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
