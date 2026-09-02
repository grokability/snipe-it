<?php

namespace App\Models\Labels\Sheets\Avery;

class L7162_B extends L7162
{
    private const BARCODE_SIZE = 6.00;

    private const BARCODE_MARGIN = 1.40;

    private const TAG_SIZE = 3.20;

    private const LOGO_MAX_WIDTH = 25.00;

    private const LOGO_MARGIN = 2.20;

    private const TITLE_SIZE = 4.20;

    private const TITLE_MARGIN = 1.20;

    private const LABEL_SIZE = 2.20;

    private const LABEL_MARGIN = -0.50;

    private const FIELD_SIZE = 4.20;

    private const FIELD_MARGIN = 0.30;

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

    public function getLogoMaxWidth()
    {
        return self::LOGO_MAX_WIDTH;
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
        return 'mm';
    }

    public function getLabelMarginTop()
    {
        return 1.0;
    }

    public function getLabelMarginBottom()
    {
        return 0;
    }

    public function getLabelMarginLeft()
    {
        return 1.0;
    }

    public function getLabelMarginRight()
    {
        return 1.0;
    }

    public function getSupportAssetTag()
    {
        return true;
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
        return 3;
    }

    public function getSupportLogo()
    {
        return true;
    }

    public function getSupportTitle()
    {
        return true;
    }

    public function getTextRenderMode(): string
    {
        return 'vertical_stack';
    }
    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_size' => 4,
            'barcode_margin' => $this->getBarcodeMargin(),
            'tag_alignment' => 'R',
            'tag_font' => 'freemono',
            'tag_font_size' => $this->getTagSize(),
            'logo_max_width' => $this->getLogoMaxWidth(),
            'title_font' => 'freesans',
            'title_font_size' => 3.2,
            'title_margin' => .2,
            'field_label_font' => 'freesans',
            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),
            'field_label_value_font' => 'freemono',
            'field_value_font_size' => 3.2,
            'field_value_margin' => $this->getFieldMargin(),
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

        $usableWidth = $pa->w;
        $usableHeight = $pa->h;
        $currentX = $pa->x1;
        $currentY = $pa->y1;

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $pa->x1, $pa->y2 - self::BARCODE_SIZE,
                $usableWidth, self::BARCODE_SIZE
            );
            $usableHeight -= self::BARCODE_SIZE + self::BARCODE_MARGIN;
        }

        if ($record->has('logo')) {
            $logoSize = static::writeImage(
                $pdf, $record->get('logo'),
                $pa->x1, $pa->y1,
                self::LOGO_MAX_WIDTH, $usableHeight,
                'L', 'T', 300, true, false, 0
            );
            $currentX += $logoSize[0] + self::LOGO_MARGIN;
            $usableWidth -= $logoSize[0] + self::LOGO_MARGIN;
        }

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', '', self::TITLE_SIZE, 'L',
                $usableWidth, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
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
                $usableWidth, self::FIELD_SIZE, true, 0, 0.3
            );
            $currentY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }

        static::writeText(
            $pdf, $record->get('tag'),
            $currentX, $pa->y2 - self::BARCODE_SIZE - self::BARCODE_MARGIN - self::TAG_SIZE,
            'freemono', 'b', self::TAG_SIZE, 'R',
            $usableWidth, self::TAG_SIZE, true, 0, 0.3
        );

    }
}
