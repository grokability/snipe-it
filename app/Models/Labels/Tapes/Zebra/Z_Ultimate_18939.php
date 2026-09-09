<?php

namespace App\Models\Labels\Tapes\Zebra;

use App\Helpers\Helper;

class Z_Ultimate_18939 extends Z_Ultimate
{
    private const WIDTH = 76.20;

    private const HEIGHT = 25.40;

    protected const BARCODE_MARGIN = 1.40;

    protected const TAG_SIZE = 2.80;
    protected const TAG_MARGIN = 1.5;

    protected const TITLE_SIZE = 2.80;

    protected const TITLE_MARGIN = 0.50;

    protected const LABEL_SIZE = 2.00;

    protected const LABEL_MARGIN = -0.35;

    protected const FIELD_SIZE = 3.20;

    protected const FIELD_MARGIN = 0.15;

    protected const LOGO_MAX_WIDTH = 20.00;

    protected const LOGO_MARGIN = 1.00;

    public function getUnit()
    {
        return 'mm';
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
        return true;
    }

    public function getSupportTitle()
    {
        return true;
    }

    public function getWidth()
    {
        return Helper::convertUnit(
            self::WIDTH,
            'mm',
            $this->getUnit()
        );
    }

    public function getHeight()
    {
        return Helper::convertUnit(
            self::HEIGHT,
            'mm',
            $this->getUnit()
        );
    }

    protected function getContentEditorConfig(): array
    {
        $pa = $this->getPrintableArea();

        return [
            'barcode_2d_size' => max(
                0,
                $pa->h - static::TAG_SIZE - static::TAG_MARGIN
            ),

            'barcode_margin' => static::BARCODE_MARGIN,

            'tag_font_size' => static::TAG_SIZE,
            'tag_margin' => static::TAG_MARGIN,
            'tag_offset_x' => 4.0,

            'title_font_size' => static::TITLE_SIZE,
            'title_margin' => static::TITLE_MARGIN,

            'field_label_font_size' => static::LABEL_SIZE,
            'field_label_margin' => static::LABEL_MARGIN,

            'field_value_font_size' => static::FIELD_SIZE,
            'field_value_margin' => static::FIELD_MARGIN,

            'logo_max_width' => static::LOGO_MAX_WIDTH,
            'logo_margin' => static::LOGO_MARGIN,

            'text_render_mode' => 'vertical_stack'
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;
        $usableWidth = $pa->w;
        $usableHeight = $pa->h;
        $currentY += 2.0;
        $barcodeSize = max(
            0,
            $usableHeight - static::TAG_SIZE - static::TAG_MARGIN
        );

        if ($record->has('barcode2d')) {
            static::writeText(
                $pdf,
                $record->get('tag'),
                $pa->x1,
                $pa->y2 - static::TAG_SIZE,
                'freemono',
                'B',
                static::TAG_SIZE,
                'C',
                $barcodeSize,
                static::TAG_SIZE,
                true,
                0
            );

            static::write2DBarcode(
                $pdf,
                $record->get('barcode2d')->content,
                $record->get('barcode2d')->type,
                $currentX,
                $currentY,
                $barcodeSize,
                $barcodeSize
            );

            $barcodeWidth = $barcodeSize + static::BARCODE_MARGIN;

            $currentX += $barcodeWidth;
            $usableWidth -= $barcodeWidth;
        } else {
            static::writeText(
                $pdf,
                $record->get('tag'),
                $pa->x1,
                $pa->y2 - static::TAG_SIZE,
                'freemono',
                'B',
                static::TAG_SIZE,
                'R',
                $usableWidth,
                static::TAG_SIZE,
                true,
                0
            );
        }
        $textWidth = $usableWidth;

        if ($record->has('logo')) {
            $textWidth -= static::LOGO_MAX_WIDTH + static::LOGO_MARGIN;
        }

        $textWidth = max(0, $textWidth);

        if ($record->has('title')) {
            static::writeText(
                $pdf,
                $record->get('title'),
                $currentX,
                $currentY,
                'freesans',
                '',
                static::TITLE_SIZE,
                'L',
                $textWidth,
                static::TITLE_SIZE,
                true,
                0
            );

            $currentY += static::TITLE_SIZE + static::TITLE_MARGIN;
        }

        foreach ($record->get('fields', []) as $field) {
            static::writeText(
                $pdf,
                $field['label'],
                $currentX,
                $currentY,
                'freesans',
                '',
                static::LABEL_SIZE,
                'L',
                $textWidth,
                static::LABEL_SIZE,
                true,
                0,
                0
            );

            $currentY += static::LABEL_SIZE + static::LABEL_MARGIN;

            static::writeText(
                $pdf,
                $field['value'],
                $currentX,
                $currentY,
                'freemono',
                'B',
                static::FIELD_SIZE,
                'L',
                $textWidth,
                static::FIELD_SIZE,
                true,
                0,
                0.3
            );

            $currentY += static::FIELD_SIZE + static::FIELD_MARGIN;
        }

        if ($record->has('logo')) {
            $logoX = $currentX + $textWidth + static::LOGO_MARGIN;

            static::writeImage(
                $pdf,
                $record->get('logo'),
                $logoX,
                $pa->y1 + 2.0,
                static::LOGO_MAX_WIDTH,
                $usableHeight,
                'L',
                'T',
                300,
                true,
                false,
                0
            );
        }
    }
}