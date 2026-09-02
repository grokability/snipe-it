<?php

namespace App\Models\Labels\Sheets\Avery;

use App\Helpers\Helper;

class L7163_A extends L7163
{
    private const BARCODE_MARGIN = 1.80;

    private const TAG_SIZE = 4.80;

    private const TITLE_SIZE = 5.00;

    private const TITLE_MARGIN = .75;

    private const LABEL_SIZE = 3.35;

    private const LABEL_MARGIN = -0.30;

    private const FIELD_SIZE = 4.80;

    private const FIELD_MARGIN = 0.20;

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
        return 'mm';
    }

    public function getLabelMarginTop()
    {
        return 1.0;
    }

    public function getLabelMarginBottom()
    {
        return 1.0;
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

    public function get2DBarcodeSize()
    {
        return 31;
    }

    protected function getContentEditorConfig(): array
    {
        return [
            'barcode_margin' => $this->getBarcodeMargin(),
            'barcode_2d_size' => $this->get2DBarcodeSize(),
            'tag_font_size' => $this->getTagSize(),
            'tag_offset_x' => 6,
            'title_font_size' => $this->getTitleSize(),
            'title_margin' => $this->getTitleMargin(),
            'field_label_font_size' => $this->getLabelSize(),
            'field_label_margin' => $this->getLabelMargin(),
            'field_value_font_size' => $this->getFieldSize(),
            'field_value_margin' => $this->getFieldMargin(),
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
                $pa->x1, $currentY,
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
        $title = $record->has('title') ? $record->get('title') : null;
        $fields = $record->get('fields');

        $field_layout = Helper::labelFieldLayoutScaling(
            pdf: $pdf,
            fields: $fields,
            currentX: $currentX,
            usableWidth: $usableWidth,
            usableHeight: $usableHeight,
            baseLabelSize: self::LABEL_SIZE,
            baseFieldSize: self::FIELD_SIZE,
            baseFieldMargin: self::FIELD_MARGIN,
            title: $title,
            baseTitleSize: self::TITLE_SIZE,
            baseTitleMargin: self::TITLE_MARGIN,
            baseLabelPadding: 1.5,
            baseGap: 1.5,
            maxScale: 1.8,
            labelFont: 'freesans',
        );

        if ($field_layout['hasTitle']) {
            static::writeText(
                $pdf, $title,
                $currentX, $currentY,
                'freesans', 'b', $field_layout['titleSize'], 'L',
                $usableWidth, $field_layout['titleSize'], true, 0
            );
            $currentY += $field_layout['titleAdvance'];
        }

        foreach ($fields as $field) {
            $rawLabel = $field['label'] ?? null;
            $value = (string) ($field['value'] ?? '');

            // No label: value takes the whole row
            if (! is_string($rawLabel) || trim($rawLabel) === '') {
                static::writeText(
                    $pdf, $value,
                    $currentX, $currentY,
                    'freemono', 'B', $field_layout['fieldSize'], 'L',
                    $usableWidth, $field_layout['rowAdvance'], true, 0, 0.01
                );

                $currentY += $field_layout['rowAdvance'];

                continue;
            }

            $labelText = rtrim($field['label'], ':').':';

            static::writeText(
                $pdf, $labelText,
                $currentX, $currentY,
                'freesans', '', $field_layout['labelSize'], 'L',
                $field_layout['labelWidth'], $field_layout['rowAdvance'], true,
            );

            static::writeText(
                $pdf, $field['value'],
                $field_layout['valueX'], $currentY,
                'freemono', 'B', $field_layout['fieldSize'], 'L',
                $field_layout['valueWidth'], $field_layout['rowAdvance'], true, 0, 0.01
            );
            $currentY += $field_layout['rowAdvance'];
        }
    }
}
