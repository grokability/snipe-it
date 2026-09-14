<?php

namespace App\Models\Labels\Sheets\Hema;

use App\Models\Labels\RectangleSheet;

/**
 * HEMA 14130046 — sheet of 10 labels (2 columns x 5 rows), each 38 x 19 mm.
 *
 * Layout: a square QR code on the left occupying nearly the full label height
 * (with a 2 mm margin all around), and the title/fields on the right.
 *
 * Geometry was measured from the manufacturer's template PDF:
 *   page      80 x 119 mm
 *   margins   left/right 1 mm, top/bottom 4 mm
 *   gutters   2 mm between columns, 4 mm between rows
 *   label     38 x 19 mm
 */
class _14130046 extends RectangleSheet
{
    /* Sheet geometry (mm) */
    private const PAGE_WIDTH = 80.0;

    private const PAGE_HEIGHT = 119.0;

    private const PAGE_MARGIN_TOP = 4.0;

    private const PAGE_MARGIN_LEFT = 2.0;

    private const LABEL_WIDTH = 38.0;

    private const LABEL_HEIGHT = 19.0;

    private const COLUMN_SPACING = 2.0;

    private const ROW_SPACING = 4.0;

    /* Content layout (mm) */
    private const CONTENT_MARGIN = 2.0;

    private const QR_GUTTER = 2.0;

    private const TITLE_SIZE = 2.6;

    private const TITLE_MARGIN = 0.6;

    private const FIELD_SIZE = 2.4;

    private const FIELD_MARGIN = 0.5;

    public function getUnit()
    {
        return 'mm';
    }

    public function getPageWidth()
    {
        return self::PAGE_WIDTH;
    }

    public function getPageHeight()
    {
        return self::PAGE_HEIGHT;
    }

    public function getPageMarginTop()
    {
        return self::PAGE_MARGIN_TOP;
    }

    public function getPageMarginBottom()
    {
        return self::PAGE_MARGIN_TOP;
    }

    public function getPageMarginLeft()
    {
        return self::PAGE_MARGIN_LEFT;
    }

    public function getPageMarginRight()
    {
        return self::PAGE_MARGIN_LEFT;
    }

    public function getColumns()
    {
        return 2;
    }

    public function getRows()
    {
        return 5;
    }

    public function getLabelColumnSpacing()
    {
        return self::COLUMN_SPACING;
    }

    public function getLabelRowSpacing()
    {
        return self::ROW_SPACING;
    }

    public function getLabelWidth()
    {
        return self::LABEL_WIDTH;
    }

    public function getLabelHeight()
    {
        return self::LABEL_HEIGHT;
    }

    public function getLabelBorder()
    {
        return 0;
    }

    public function getLabelMarginTop()
    {
        return 0;
    }

    public function getLabelMarginBottom()
    {
        return 0;
    }

    public function getLabelMarginLeft()
    {
        return 0;
    }

    public function getLabelMarginRight()
    {
        return 0;
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
        return 4;
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
            'barcode_2d_size' => $this->getLabelHeight() - (2 * self::CONTENT_MARGIN),
            'barcode_margin' => self::QR_GUTTER,

            'title_font_size' => self::TITLE_SIZE,
            'title_margin' => self::TITLE_MARGIN,

            'field_label_font_size' => self::FIELD_SIZE,
            'field_label_margin' => 0,
            'field_label_font' => 'freesans',

            'field_value_font_size' => 1.4,
            'field_value_margin' => self::FIELD_MARGIN,
            'field_value_font' => 'freesans',
        ];
    }
    public function write($pdf, $record)
    {
        // The QR code is a square occupying (nearly) the full label height,
        // with a CONTENT_MARGIN margin all around it, pinned to the left.
        $qrSize = $this->getLabelHeight() - (2 * self::CONTENT_MARGIN);
        $qrX = self::CONTENT_MARGIN;
        $qrY = self::CONTENT_MARGIN;

        if ($record->get('barcode2d')) {
            static::write2DBarcode(
                $pdf, $record->get('barcode2d')->content, $record->get('barcode2d')->type,
                $qrX, $qrY, $qrSize, $qrSize
            );
        }

        // Fields sit to the right of the QR code.
        $textX = $qrX + $qrSize + self::QR_GUTTER;
        $textW = $this->getLabelWidth() - self::CONTENT_MARGIN - $textX;
        $textY = self::CONTENT_MARGIN;

        if ($record->get('title')) {
            static::writeText(
                $pdf, $record->get('title'),
                $textX, $textY,
                'freesans', 'b', self::TITLE_SIZE, 'L',
                $textW, self::TITLE_SIZE, true, 0
            );
            $textY += self::TITLE_SIZE + self::TITLE_MARGIN;
        }

        foreach ($record->get('fields') as $field) {
            static::writeText(
                $pdf, (($field['label']) ? $field['label'].' ' : '').$field['value'],
                $textX, $textY,
                'freesans', '', self::FIELD_SIZE, 'L',
                $textW, self::FIELD_SIZE, true, 0
            );
            $textY += self::FIELD_SIZE + self::FIELD_MARGIN;
        }
    }
}
