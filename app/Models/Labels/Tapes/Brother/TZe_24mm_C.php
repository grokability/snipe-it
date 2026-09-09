<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_24mm_C extends TZe_24mm
{
    private const BARCODE_MARGIN = 1.40;

    private const TAG_SIZE = 4.00;

    private const LOGO_MAX_WIDTH = 13.50;

    private const LOGO_MARGIN = 2.20;

    private const TITLE_SIZE = 2.80;

    private const TITLE_MARGIN = 0.50;

    private const LABEL_SIZE = 2.00;

    private const LABEL_MARGIN = -0.35;

    private const FIELD_SIZE = 3.20;

    private const FIELD_MARGIN = 0.15;

    public function getBarcodeMargin(): float
    {
        return self::BARCODE_MARGIN;
    }

    public function getTagSize(): float
    {
        return self::TAG_SIZE;
    }

    public function getLogoMaxWidth(): float
    {
        return self::LOGO_MAX_WIDTH;
    }

    public function getLogoMargin(): float
    {
        return self::LOGO_MARGIN;
    }

    public function getTitleSize(): float
    {
        return self::TITLE_SIZE;
    }

    public function getTitleMargin(): float
    {
        return self::TITLE_MARGIN;
    }

    public function getLabelSize(): float
    {
        return self::LABEL_SIZE;
    }

    public function getLabelMargin(): float
    {
        return self::LABEL_MARGIN;
    }

    public function getFieldSize(): float
    {
        return self::FIELD_SIZE;
    }

    public function getFieldMargin(): float
    {
        return self::FIELD_MARGIN;
    }

    public function getUnit()
    {
        return 'mm';
    }

    public function getWidth()
    {
        return 34.0;
    }

    public function getSupportAssetTag()
    {
        return true;
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
        return 0;
    }

    public function getSupportLogo()
    {
        return true;
    }

    public function getSupportTitle()
    {
        return false;
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_margin' => $this->getBarcodeMargin(),
            'barcode_2d_size' => 12,

            'tag_font_size' => $this->getTagSize(),

            'logo_max_width' => $this->getLogoMaxWidth(),
            'logo_margin' => $this->getLogoMargin(),

            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),

            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),

            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),

            'tag_alignment' => 'L',
            'logo_h_align' => 'R',
            'text_render_mode' => 'block',
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;

        $barcodeSize = $pa->h - self::TAG_SIZE;

        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freemono', 'b', self::TAG_SIZE, 'C',
                $barcodeSize, self::TAG_SIZE, true, 0
            );
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $currentY,
                $barcodeSize, $barcodeSize
            );
            $currentX += $barcodeSize + self::BARCODE_MARGIN;
            $usableWidth -= $barcodeSize + self::BARCODE_MARGIN;
        } else {
            static::writeText(
                $pdf, $record->get('tag'),
                $pa->x1, $pa->y2 - self::TAG_SIZE,
                'freemono', 'b', self::TAG_SIZE, 'R',
                $usableWidth, self::TAG_SIZE, true, 0
            );
        }

        $usableWidth -= self::LOGO_MAX_WIDTH - self::LOGO_MARGIN;

        $currentX += $usableWidth - (self::LOGO_MARGIN / 2);

        if ($record->has('logo')) {
            $logoSize = static::writeImage(
                $pdf, $record->get('logo'),
                $currentX, $pa->y1,
                self::LOGO_MAX_WIDTH, $usableHeight,
                'L', 'T', 300, true, false, 0
            );
            $currentX += $logoSize[0] + self::LOGO_MARGIN;
            $usableWidth -= $logoSize[0] + self::LOGO_MARGIN;
        }
    }
}
