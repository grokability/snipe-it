<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_24mm_A extends TZe_24mm
{
    private const BARCODE_MARGIN = 1.40;

    private const TAG_SIZE = 2.80;

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
        return 65.0;
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

    public function getTextRenderMode(): string
    {
        return 'vertical_stack';
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_margin' => $this->getBarcodeMargin(),
            'barcode_2d_size' => 15,

            'tag_font_size' => $this->getTagSize(),
            'tag_offset_x' => 2,

            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),

            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),

            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),
            'text_render_mode' => $this->getTextRenderMode(),
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;

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
                $usableWidth, self::LABEL_SIZE, true, 0, 0
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
    }
}
