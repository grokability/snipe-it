<?php

namespace App\Models\Labels\Tapes\Brother;

class TZe_62mm_Landscape_A extends TZe_62mm_Landscape
{
    public function getUnit()
    {
        return 'mm';
    }

    public function getHeight()
    {
        return 31.50;
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
        return true;
    }

    public function getSupportFields()
    {
        return 2;
    }

    public function getSupportLogo()
    {
        return true;
    }

    public function getSupportTitle()
    {
        return true;
    }

    private const BARCODE1D_HEIGHT = 3.00;

    private const BARCODE1D_MARGIN = 3.00;

    private const BARCODE2D_SIZE = 20.00;

    private const BARCODE2D_MARGIN = 1.50;

    private const TAG_SIZE = 3.00;

    private const LOGO_HEIGHT = 10.00;

    private const LOGO_MARGIN = 1.50;

    private const TITLE_SIZE = 3.00;

    private const TITLE_MARGIN = 0.50;

    private const LABEL_SIZE = 2.00;

    private const LABEL_MARGIN = -0.35;

    private const FIELD_SIZE = 3.00;

    private const FIELD_MARGIN = 0.10;

    public function getBarcodeSize(): float
    {
        return self::BARCODE1D_HEIGHT;
    }

    public function getBarcodeMargin(): float
    {
        return self::BARCODE1D_MARGIN;
    }

    public function get2DBarcodeSize(): float
    {
        return self::BARCODE2D_SIZE;
    }

    public function getBarcode2DMargin(): float
    {
        return self::BARCODE2D_MARGIN;
    }

    public function getTagSize(): float
    {
        return self::TAG_SIZE;
    }

    public function getLogoMaxHeight(): float
    {
        return self::LOGO_HEIGHT;
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

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_size' => $this->getBarcodeSize(),
            'barcode_margin' => $this->getBarcodeMargin(),
            'barcode1D_v_align' => 'T',

            'barcode_2d_size' => $this->get2DBarcodeSize(),
            'barcode_2d_margin' => $this->getBarcode2DMargin(),
            'barcode2D_h_align' => 'L',
            'barcode2D_v_align' => 'T',

            'tag_font_size' => $this->getTagSize(),
            'tag_alignment' => 'C',
            'tag_position_mode' => 'under_barcode',

            'logo_max_height' => $this->getLogoMaxHeight(),
            'logo_margin' => $this->getLogoMargin(),
            'logo_h_align' => 'L',
            'logo_v_align' => 'T',
            "logo_placement" => 'text_column',

            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),

            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),

            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),

            'text_render_mode' => 'block',
        ];
    }

    public function write($pdf, $record)
    {
        $pa = $this->getPrintableArea();

        $currentX = $pa->x1;
        $currentY = $pa->y1;

        // Wide 1D barcode on top

        if ($record->has('barcode1d')) {
            static::write1DBarcode(
                $pdf, $record->get('barcode1d')->content, $record->get('barcode1d')->type,
                $currentX, $currentY, $pa->w, self::BARCODE1D_HEIGHT
            );
            $currentY = self::BARCODE1D_HEIGHT + self::BARCODE1D_MARGIN;
        }

        // Left column

        if ($record->has('barcode2d')) {
            $columnY = $currentY;
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $currentX, $columnY,
                self::BARCODE2D_SIZE, self::BARCODE2D_SIZE
            );
            $columnY += self::BARCODE2D_SIZE + self::BARCODE2D_MARGIN;
            static::writeText(
                $pdf, $record->get('tag'),
                $currentX, $columnY,
                'freemono', 'b', self::TAG_SIZE, 'C',
                self::BARCODE2D_SIZE, self::TAG_SIZE, true, 0
            );
            $currentX += self::BARCODE2D_SIZE + self::BARCODE2D_MARGIN;
        }

        // Right column
        if ($record->get('logo')) {
            static::writeImage(
                $pdf, $record->get('logo'),
                $currentX, $currentY,
                $pa->w - $currentX, self::LOGO_HEIGHT,
                'L', 'T', 300, true, false, 0
            );
            $currentY += self::LOGO_HEIGHT + self::LOGO_MARGIN;
        }

        if ($record->has('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $currentX, $currentY,
                'freesans', '', self::TITLE_SIZE, 'L',
                $pa->w - $currentX, self::TITLE_SIZE, true, 0
            );
            $currentY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        foreach ($record->get('fields') as $field) {
            static::writeText(
                $pdf, $field['label'],
                $currentX, $currentY,
                'freesans', '', self::LABEL_SIZE, 'L',
                $pa->w - $currentX, self::LABEL_SIZE, true, 0, 0
            );
            $currentY += self::LABEL_SIZE + self::LABEL_MARGIN;

            static::writeText(
                $pdf, $field['value'],
                $currentX, $currentY,
                'freemono', 'B', self::FIELD_SIZE, 'L',
                $pa->w - $currentX, self::FIELD_SIZE, true, 0, 0.3
            );
            $currentY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }
    }
}
