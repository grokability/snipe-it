<?php

namespace App\Models\Labels\Sheets\Hema;

use App\Models\Labels\RectangleSheet;

/**
 * HEMA 38310012 — A4 sheet of 21 labels (3 columns x 7 rows), each 63.5 x 38.1 mm.
 *
 * Layout: a square QR code on the left occupying nearly the full label height
 * (with a 2 mm margin all around), and the title/fields on the right.
 *
 * Geometry (mm):
 *   page      210 x 297 mm (A4 portrait)
 *   margins   left/right 6 mm, top/bottom 13 mm
 *   gutters   3 mm between columns, 0 mm between rows
 *   label     63.5 x 38.1 mm
 */
class _38310012 extends RectangleSheet
{
    /* Sheet geometry (mm) */
    private const PAGE_WIDTH = 210.0;

    private const PAGE_HEIGHT = 297.0;

    private const PAGE_MARGIN_TOP = 13.0;

    private const PAGE_MARGIN_LEFT = 6.0;

    private const LABEL_WIDTH = 63.5;

    private const LABEL_HEIGHT = 38.1;

    private const COLUMN_SPACING = 3.0;

    private const ROW_SPACING = 0.0;

    /* Content layout (mm) */
    private const CONTENT_MARGIN = 2.0;

    private const QR_GUTTER = 3.0;

    private const TITLE_SIZE = 3.6;

    private const TITLE_MARGIN = 1.0;

    private const FIELD_SIZE = 3.0;

    private const FIELD_MARGIN = 0.8;

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
        return 3;
    }

    public function getRows()
    {
        return 7;
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
        return 5;
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

            'title_font_size' => 2.6,
            'title_margin' => self::TITLE_MARGIN,

            'field_label_font_size' => 2.0,
            'field_label_margin' => 0,
            'field_label_font' => 'freesans',

            'field_value_font_size' => 2.0,
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
